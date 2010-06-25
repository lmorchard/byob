/**
 * Copyright (c) 2010 Appcoast Ltd. All rights reserved.
 */

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

    AddonInstaller.ObserverService.addObserver(
      this, "profile-change-teardown", false);
  },

  /**
   * Prevents the addon manager window from being showed by removing the value.
   */
  preventAddonManager : function() {
    this._logger.debug("preventAddonManager");

    let preventPref =
      AddonInstaller.Application.prefs.get(this._PREVENT_PREFKEY);

    if (preventPref && preventPref.value) {
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
      if (defaultBrowser) {
        AddonInstaller.Application.prefs.setValue(
          "browser.shell.checkDefaultBrowser", defaultBrowser.value);
        this._preferenceService.clearUserPref(this._DEFAULTBROWSER_PREFKEY);
      }
    }
  },

  /**
   * Starts the install process.
   */
  startInstallProcess : function() {
    this._logger.debug("startInstallProcess");

    let globalPreference =
      AddonInstaller.Application.prefs.get(this._GLOBLAL_PREFKEY);
    let maxRetryPref =
      AddonInstaller.Application.prefs.get(this._MAX_RETRY_PREFKEY);
    let curRetryPref =
      AddonInstaller.Application.prefs.get(this._CUR_RETRY_PREFKEY);
    let maxRetry = (maxRetryPref ? maxRetryPref.value : 0);
    let curRetry = (curRetryPref ? curRetryPref.value : 0);

    if (globalPreference && !globalPreference.value && curRetry < maxRetry) {
      this._openLoadingWindow();
      this._installExtensions();
    }
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
   * Installs the extensions.
   */
  _installExtensions : function() {
    this._logger.trace("_installExtensions");

    let extensionArray = this._getExtensionsToInstall();
    let extensionInstalled = null;
    let allExtensionsInstalled = true;

    try {
      for (let i = 0; i < extensionArray.length; i++) {
        extensionInstalled = this._installExtension(extensionArray[i]);

        if (!extensionInstalled) {
          allExtensionsInstalled = false;
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
        extInfo = {};

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

    return extensions;
  },

  /**
   * Sets the preferences before restart.
   */
  _setRestartPreferences: function() {
    this._logger.trace("_setRestartPreferences");

    let warnOnRestart =
      AddonInstaller.Application.prefs.get("browser.warnOnRestart").value;
    let defaultBrowser =
      AddonInstaller.Application.prefs.get(
        "browser.shell.checkDefaultBrowser").value;

    AddonInstaller.Application.prefs.setValue(this._PREVENT_PREFKEY, true);
    AddonInstaller.Application.prefs.setValue(
      this._WARNONRESTART_PREFKEY, warnOnRestart);
    AddonInstaller.Application.prefs.setValue(
      this._DEFAULTBROWSER_PREFKEY, defaultBrowser);
    AddonInstaller.Application.prefs.setValue("browser.warnOnRestart", false);
    AddonInstaller.Application.prefs.setValue("browser.shell.checkDefaultBrowser", false);
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
