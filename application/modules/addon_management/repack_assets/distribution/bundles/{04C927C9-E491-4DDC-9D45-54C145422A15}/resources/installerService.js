/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Add-on Installer.
 *
 * The Initial Developer of the Original Code is Appcoast.
 * Portions created by the Initial Developer are Copyright (C) 2009
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *   Andres Hernandez <andres@appcoast.com>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */

var EXPORTED_SYMBOLS = [];

const Cc = Components.classes;
const Ci = Components.interfaces;
const Cu = Components.utils;

Cu.import("resource://addoninstaller/common.js");

/**
 * Installer service.
 */
AddonInstaller.InstallerService = {
  /* Global preference key. */
  _GLOBLAL_PREFKEY : AddonInstaller.PrefBranch + "installation.completed",
  /* Prevent addon manager preference key. */
  _PREVENT_PREFKEY : AddonInstaller.PrefBranch + "prevent.addonManager",
  /* Store the warn on restart preference key. */
  _WARNONRESTART_PREFKEY : AddonInstaller.PrefBranch + "store.warnOnRestart",
  /* Store the check default browser preference key. */
  _DEFAULTBROWSER_PREFKEY : AddonInstaller.PrefBranch + "store.defaultBrowser",
  /* Stores the max number of restarts in case of error. */
  _MAX_RETRY_PREFKEY : AddonInstaller.PrefBranch + "retry.maxRestarts",
  /* Stores the current number of restarts in case of error. */
  _CUR_RETRY_PREFKEY : AddonInstaller.PrefBranch + "retry.curRestarts",
  /* Flag to show the loading the dialog. */
  _SHOW_LOADING_PREFKEY : AddonInstaller.PrefBranch + "show.loadingDialog",
  /* Flag to show the selection the dialog. */
  _SHOW_SELECTION_PREFKEY : AddonInstaller.PrefBranch + "show.selectionDialog",

  /* Logger for this object. */
  _logger : null,
  /* Extension manager. */
  _extensionManager : null,
  /* Version comparator. */
  _versionComparator : null,
  /* Preference service. */
  _preferenceService : null,
  /* Xul runtime service. */
  _xulRuntime : null,

  /**
   * Initializes the component.
   */
  _init : function() {
    this._logger = AddonInstaller.getLogger("AddonInstaller.InstallerService");
    this._logger.trace("_init");

    this._extensionManager =
      Cc["@mozilla.org/extensions/manager;1"].
        getService(Ci.nsIExtensionManager);
    this._versionComparator =
      Cc["@mozilla.org/xpcom/version-comparator;1"].
        getService(Ci.nsIVersionComparator);
    this._preferenceService =
      Cc["@mozilla.org/preferences-service;1"].getService(Ci.nsIPrefBranch);
    this._xulRuntime =
      Cc["@mozilla.org/xre/app-info;1"].getService(Ci.nsIXULRuntime);
  },

  /**
   * Prevents the addon manager window from being showed by removing the value.
   */
  preventAddonManager : function() {
    this._logger.debug("preventAddonManager");

    let preventPref =
      AddonInstaller.Application.prefs.get(this._PREVENT_PREFKEY);

    if (preventPref && preventPref.value) {
      let that = this;
      let timer = Cc["@mozilla.org/timer;1"].createInstance(Ci.nsITimer);
      let newAddonsPref =
        AddonInstaller.Application.prefs.get("extensions.newAddons");
      let warnOnRestart =
        AddonInstaller.Application.prefs.get(this._WARNONRESTART_PREFKEY);
      let defaultBrowser =
        AddonInstaller.Application.prefs.get(this._DEFAULTBROWSER_PREFKEY);

      this._preferenceService.clearUserPref(this._PREVENT_PREFKEY);

      if (newAddonsPref) {
        this._preferenceService.clearUserPref("extensions.newAddons");
      }
      if (warnOnRestart) {
        AddonInstaller.Application.prefs.setValue(
          "browser.warnOnRestart", warnOnRestart.value);
        this._preferenceService.clearUserPref(this._WARNONRESTART_PREFKEY);
      }

      timer.initWithCallback(
        { notify : function() { that._selectFirstRunTab(); } },
        1500, Ci.nsITimer.TYPE_ONE_SHOT);
    }
  },

  /**
   * Re-selects the first run tab after opening the main window.
   */
  _selectFirstRunTab : function() {
    this._logger.trace("_selectFirstRunTab");

    let defaultBrowser =
      AddonInstaller.Application.prefs.get(this._DEFAULTBROWSER_PREFKEY);
    let formatter =
      Cc["@mozilla.org/toolkit/URLFormatterService;1"].
        getService(Ci.nsIURLFormatter);
    let windowMediator =
      Cc["@mozilla.org/appshell/window-mediator;1"].
        getService(Ci.nsIWindowMediator);
    let win = windowMediator.getMostRecentWindow("navigator:browser");
    let firstRunPage = formatter.formatURLPref("startup.homepage_welcome_url");
    let tabs = win.gBrowser.tabContainer.childNodes;
    let tab = null;

    for (let i = 0; i < tabs.length; i++) {
      tab = tabs[i];

      if (firstRunPage == tab.linkedBrowser.currentURI.spec) {
        win.gBrowser.selectedTab = tab;
        break;
      }
    }

    if (defaultBrowser) {
      AddonInstaller.Application.prefs.setValue(
        "browser.shell.checkDefaultBrowser", defaultBrowser.value);
      this._preferenceService.clearUserPref(this._DEFAULTBROWSER_PREFKEY);
    }
  },

  /**
   * Check if the install process should start.
   */
  checkInstallProcess : function() {
    this._logger.debug("checkInstallProcess");

    let globalPreference =
      AddonInstaller.Application.prefs.get(this._GLOBLAL_PREFKEY);
    let maxRetryPref =
      AddonInstaller.Application.prefs.get(this._MAX_RETRY_PREFKEY);
    let curRetryPref =
      AddonInstaller.Application.prefs.get(this._CUR_RETRY_PREFKEY);
    let maxRetry = (maxRetryPref ? maxRetryPref.value : 0);
    let curRetry = (curRetryPref ? curRetryPref.value : 0);

    if (globalPreference && !globalPreference.value && curRetry < maxRetry) {
      this._startInstallProcess();
    }
  },

  /**
   * Starts the install process.
   */
  _startInstallProcess : function() {
    this._logger.trace("_startInstallProcess");

    let showSelectionDialogPref =
      AddonInstaller.Application.prefs.get(this._SHOW_SELECTION_PREFKEY);
    let showLoadingDialogPref =
      AddonInstaller.Application.prefs.get(this._SHOW_LOADING_PREFKEY);
    let defaultBrowserPref =
      AddonInstaller.Application.prefs.get("browser.shell.checkDefaultBrowser");
    let extensionArray = this._getExtensionsToInstall();

    if (defaultBrowserPref && defaultBrowserPref.value) {
      AddonInstaller.Application.prefs.setValue(
        this._DEFAULTBROWSER_PREFKEY, defaultBrowserPref.value);
      AddonInstaller.Application.prefs.setValue(
        "browser.shell.checkDefaultBrowser", false);
    }

    if (showSelectionDialogPref && showSelectionDialogPref.value) {
      extensionArray = this._openSelectionWindow(extensionArray);
    }
    if (showLoadingDialogPref && showLoadingDialogPref.value) {
      this._openLoadingWindow();
    }
    this._installExtensions(extensionArray);
  },

  /**
   * Opens the loading window.
   */
  _openLoadingWindow : function() {
    this._logger.trace("_openLoadingWindow");

    let windowWatcher =
      Cc["@mozilla.org/embedcomp/window-watcher;1"].
        getService(Ci.nsIWindowWatcher);
    let loadingDialog = windowWatcher.openWindow(
      null,"chrome://addoninstaller/content/loading.xul",
      "addon-installer-loading-dialog", "chrome,centerscreen,resizable=no",
      null);

    loadingDialog.focus();
  },

  /**
   * Opens the selection window.
   * @param aExtensionList the extension list.
   */
  _openSelectionWindow : function(aExtensionList) {
    this._logger.trace("_openSelectionWindow");

    let windowWatcher =
      Cc["@mozilla.org/embedcomp/window-watcher;1"].
        getService(Ci.nsIWindowWatcher);
    let selectionDialog = windowWatcher.openWindow(
      null,"chrome://addoninstaller/content/selection.xul",
      "addon-installer-selection-dialog",
      "chrome,modal,centerscreen,resizable=no", aExtensionList);

    return aExtensionList;
  },

  /**
   * Installs the extensions.
   * @param aExtensionList the extension list.
   */
  _installExtensions : function(aExtensionList) {
    this._logger.trace("_installExtensions");

    let extensionInstalled = null;
    let allExtensionsInstalled = true;

    try {
      for (let i = 0; i < aExtensionList.length; i++) {
        if (aExtensionList[i].install) {
          extensionInstalled = this._installExtension(aExtensionList[i]);

          if (!extensionInstalled) {
            allExtensionsInstalled = false;
          }
        }
      }

      if (allExtensionsInstalled) { // process finished.
        AddonInstaller.Application.prefs.setValue(this._GLOBLAL_PREFKEY, true);
      } else { // unfinished, need retry.
        let maxRetryPref =
          AddonInstaller.Application.prefs.get(this._MAX_RETRY_PREFKEY);
        let curRetryPref =
          AddonInstaller.Application.prefs.get(this._CUR_RETRY_PREFKEY);
        let maxRetry = (maxRetryPref ? maxRetryPref.value : 0);
        let curRetry = (curRetryPref ? curRetryPref.value : 0);

        AddonInstaller.Application.prefs.setValue(
          this._CUR_RETRY_PREFKEY, ++curRetry);

        if (curRetry >= maxRetry) { // max retries reached, process finished.
          AddonInstaller.Application.prefs.setValue(
            this._GLOBLAL_PREFKEY, true);
        }
      }

      AddonInstaller.ObserverService.addObserver(
        this, "profile-change-teardown", false);
      this._setRestartPreferences();
      this._restartFirefox();
    } catch (e) {
      this._logger.error("_installExtensions:\n" + e);
    }
  },

  /**
   * Install a extension.
   * @param aExtInfo the extension info.
   * @return true if everything finished fine, false otherwise.
   */
  _installExtension : function(aExtInfo) {
    this._logger.trace("_installExtension");

    let extensionInstalled = true;

    if (null != aExtInfo && "" != aExtInfo.id &&
        this._shouldInstall(aExtInfo)) {
      let extPrefKey = AddonInstaller.PrefBranch + aExtInfo.id;
      let extFile = AddonInstaller.getExtensionsDirectory();
      let extItem = null;

      try {
        extFile.append(aExtInfo.file);

        if (extFile.exists()) {
          this._extensionManager.installItemFromFile(extFile, "app-profile");

          // verify if was installed.
          extItem = this._extensionManager.getItemForID(aExtInfo.id);

          if (null == extItem) { // not installed.
            this._logger.error(
              "_installExtension:\n The installation verification failed.");
            extensionInstalled = false;
            AddonInstaller.Application.prefs.setValue(extPrefKey, false);
          } else { // installed.
            AddonInstaller.Application.prefs.setValue(extPrefKey, true);
          }
        } else {
          this._logger.error(
            "_installExtension:\n File doesn't exists: " + extFile.path);
          extensionInstalled = false;
          AddonInstaller.Application.prefs.setValue(extPrefKey, false);
        }
      } catch (e) {
        this._logger.error("_installExtension:\n" + e);

        extensionInstalled = false;
        AddonInstaller.Application.prefs.setValue(extPrefKey, false);
      }
    }

    return extensionInstalled;
  },

  /**
   * Verifies if the extension should be installed.
   * @param aExtInfo the extension info.
   * @return true if should install, false otherwise
   */
  _shouldInstall : function(aExtInfo) {
    this._logger.trace("_shouldInstall");

    let shouldInstall = false;

    if (this._isOSCompatible(aExtInfo) && this._isLocaleCompatible(aExtInfo)) {
      let extPrefKey = AddonInstaller.PrefBranch + aExtInfo.id;
      let extPref = AddonInstaller.Application.prefs.get(extPrefKey);

      if (!extPref || !extPref.value) { // it wasn't being installed previously.
        let extItem = this._extensionManager.getItemForID(aExtInfo.id);

        if (null == extItem) { // not installed.
          shouldInstall = true;
        }  else { // already installed, compare version.
          let comparison =
            this._versionComparator.compare(extItem.version, aExtInfo.version);

          if (0 > comparison) {
            shouldInstall = true;
          }
        }
      }
    }

    return shouldInstall;
  },

  /**
   * Verifies if the extension is compatible with the OS.
   * @param aExtInfo the extension info.
   * @return true if compatible, false otherwise.
   */
  _isOSCompatible : function(aExtInfo) {
    this._logger.trace("_isOSCompatible");

    let compatible = false;
    let xulOS = this._xulRuntime.OS;
    let extOS = aExtInfo.OS.replace(/^\s+|\s+$/g, '');

    if ("all" == extOS || -1 != extOS.indexOf(xulOS)) {
      compatible = true;
    }

    return compatible;
  },

  /**
   * Verifies if the extension is compatible with the locale.
   * @param aExtInfo the extension info.
   * @return true if compatible, false otherwise.
   */
  _isLocaleCompatible : function(aExtInfo) {
    this._logger.trace("_isLocaleCompatible");

    let compatible = false;
    let xulLocale =
      AddonInstaller.Application.prefs.get("general.useragent.locale").value;
    let extLocaleInc = aExtInfo.localeInc.replace(/^\s+|\s+$/g, '');
    let extLocaleExc = aExtInfo.localeExc.replace(/^\s+|\s+$/g, '');

    if ("all" == extLocaleInc && -1 == extLocaleExc.indexOf(xulLocale)) {
      compatible = true;
    } else if (-1 != extLocaleInc.indexOf(xulLocale)) {
      compatible = true;
    }

    return compatible;
  },

  /**
   * Gets the extensions to install.
   * @return the array of extensions to install.
   */
  _getExtensionsToInstall : function() {
    this._logger.trace("_getExtensionsToInstall");

    let extensions = new Array();
    let configFile = AddonInstaller.getExtensionsDirectory();

    configFile.append("config.ini");

    if (configFile.exists() && 0 < configFile.fileSize) {
      let iniParser =
        Cc["@mozilla.org/xpcom/ini-parser-factory;1"].
          getService(Ci.nsIINIParserFactory).createINIParser(configFile);
      let sections = iniParser.getSections();
      let section = null;
      let extInfo = null;

      while (sections.hasMore()) {
        section = sections.getNext();
        extInfo = { install : true };

        try {
          extInfo.name = iniParser.getString(section,"name");
        } catch (e) {
          extInfo.name = "";
          this._logger.error("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.id = iniParser.getString(section,"id");
        } catch (e) {
          extInfo.id = "";
          this._logger.error("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.file = iniParser.getString(section,"file");
        } catch (e) {
          extInfo.file = "";
          this._logger.error("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.version = iniParser.getString(section,"version");
        } catch (e) {
          extInfo.version = "";
          this._logger.warn("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.OS = iniParser.getString(section,"os");
        } catch (e) {
          extInfo.OS = "all";
          this._logger.info("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.localeInc = iniParser.getString(section,"locale_inclusion");
        } catch (e) {
          extInfo.localeInc = "all";
          this._logger.info("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.localeExc = iniParser.getString(section,"locale_exclusion");
        } catch (e) {
          extInfo.localeExc = "";
          this._logger.info("_getExtensionsToInstall:\n" + e);
        }

        extensions.push(extInfo);
      }
    } else {
      this._logger.error("_getExtensionsToInstall: config.ini not found!");
    }

    extensions.wrappedJSObject = extensions;

    return extensions;
  },

  /**
   * Sets the preferences before restart.
   */
  _setRestartPreferences: function() {
    this._logger.trace("_setRestartPreferences");

    let warnOnRestart =
      AddonInstaller.Application.prefs.get("browser.warnOnRestart").value;

    AddonInstaller.Application.prefs.setValue(
      this._WARNONRESTART_PREFKEY, warnOnRestart);
    AddonInstaller.Application.prefs.setValue("browser.warnOnRestart", false);
    AddonInstaller.Application.prefs.setValue(this._PREVENT_PREFKEY, true);
  },

  /**
   * Restarts firefox.
   * Notify all windows that an application quit has been requested.
   */
  _restartFirefox : function() {
    this._logger.trace("_restartFirefox");

    let restartService =
      Cc["@mozilla.org/toolkit/app-startup;1"].getService(Ci.nsIAppStartup);
    let cancelQuit =
      Cc["@mozilla.org/supports-PRBool;1"].createInstance(Ci.nsISupportsPRBool);

    AddonInstaller.ObserverService.notifyObservers(
      cancelQuit, "quit-application-requested", "restart");

    // something aborted the quit process.
    if (cancelQuit.data) {
      return;
    }

    restartService.quit(
      Ci.nsIAppStartup.eRestart | Ci.nsIAppStartup.eAttemptQuit);
  },

  /**
   * Observes global topic changes.
   * @param aSubject the object that experienced the change.
   * @param aTopic the topic being observed.
   * @param aData the data relating to the change.
   */
  observe : function(aSubject, aTopic, aData) {
    this._logger.debug("observe");

    if ("profile-change-teardown" == aTopic) {
      AddonInstaller.Application.prefs.setValue(
        "browser.sessionstore.resume_session_once", false);
      this._preferenceService.clearUserPref(
        "browser.startup.homepage_override.mstone");
    }
  }
};

/**
 * Constructor.
 */
(function() {
  this._init();
}).apply(AddonInstaller.InstallerService);
