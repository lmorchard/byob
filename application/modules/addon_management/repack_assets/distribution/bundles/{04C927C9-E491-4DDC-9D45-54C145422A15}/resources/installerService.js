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
AppcAddi.InstallerService = {
  /* Global preference key. */
  _GLOBAL_PREFERENCE_KEY : AppcAddi.PrefBranch + "installation.completed",
  /* Prevent addon manager preference key. */
  _PREVENT_PREFERENCE_KEY : AppcAddi.PrefBranch + "prevent.addonManager",

  /* Logger for this object. */
  _logger : null,
  /* Extension manager. */
  _extensionManager : null,

  /**
   * Initializes the component.
   */
  _init : function() {
    this._logger = AppcAddi.getLogger("AppcAddi.InstallerService");
    this._logger.trace("_init");

    this._extensionManager =
      Cc["@mozilla.org/extensions/manager;1"].
        getService(Ci.nsIExtensionManager);
  },

  /**
   * Prevents the addon manager window from being showed by removing the value.
   */
  preventAddonManager : function() {
    this._logger.debug("preventAddonManager");

    let preventPref =
      AppcAddi.Application.prefs.get(this._PREVENT_PREFERENCE_KEY);

    if (preventPref && preventPref.value) {
      let prefBranch =
        Cc["@mozilla.org/preferences-service;1"].getService(Ci.nsIPrefBranch);
      let newAddonsPref =
        AppcAddi.Application.prefs.get("extensions.newAddons");

      prefBranch.clearUserPref(this._PREVENT_PREFERENCE_KEY);

      if (newAddonsPref) {
        prefBranch.clearUserPref("extensions.newAddons");
      }
    }
  },

  /**
   * Starts the install process.
   */
  startInstallProcess : function() {
    this._logger.debug("startInstallProcess");

    let globalPreference =
      AppcAddi.Application.prefs.get(this._GLOBAL_PREFERENCE_KEY);

    if (globalPreference && !globalPreference.value) {
      this._openLoadingWindow();
      this._installExtensions();
    }
  },

  /**
   * Installs the extensions.
   */
  _installExtensions : function() {
    this._logger.trace("_installExtensions");

    let extensionArray = this._getExtensionsToInstall();
    let processWithoutErrors = true;

    try {
      for (let i = 0; i < extensionArray.length; i++) {
        processWithoutErrors = this._installExtension(extensionArray[i]);
      }

      if (processWithoutErrors) {
        AppcAddi.Application.prefs.setValue(this._GLOBAL_PREFERENCE_KEY, true);
        AppcAddi.Application.prefs.setValue(this._PREVENT_PREFERENCE_KEY, true);
        this._restartFirefox();
      }
    } catch (e) {
      this._logger.error("_installExtensions:\n" + e);
    }
  },

  /**
   * Install a extension.
   * @param aExtensionInfo the extension info.
   * @return true if everything finished fine, false otherwise.
   */
  _installExtension : function(aExtensionInfo) {
    this._logger.trace("_installExtension");

    let processSuccess = true;

    if (null != aExtensionInfo) {
      let preferenceKey = AppcAddi.PrefBranch + aExtensionInfo.extensionId;

      try {
        let extension =
          this._extensionManager.getItemForID(aExtensionInfo.extensionId);

        if (null == extension) { // not installed
          let extensionFile = AppcAddi.getExtensionsDirectory();

          extensionFile.append(aExtensionInfo.extensionFile);
          this._extensionManager.installItemFromFile(
            extensionFile, "app-profile");

          AppcAddi.Application.prefs.setValue(preferenceKey, true);
        } else { // already installed
        }
      } catch (e) {
        this._logger.error("_installExtension:\n" + e);

        processSuccess = false;
        AppcAddi.Application.prefs.setValue(preferenceKey, false);
      }
    }

    return processSuccess;
  },

  /**
   * Gets the extensions to install.
   * @return the array of extensions to install.
   */
  _getExtensionsToInstall : function() {
    this._logger.trace("_getExtensionsToInstall");

    let extensions = new Array();
    let configFile = AppcAddi.getExtensionsDirectory();

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
          extInfo.extensionId = iniParser.getString(section,"ExtensionId");
        } catch (e) {
          extInfo.extensionId = "";
          this._logger.warn("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.extensionFile = iniParser.getString(section,"ExtensionFile");
        } catch (e) {
          extInfo.extensionFile = "";
          this._logger.warn("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.version = iniParser.getString(section,"Version");
        } catch (e) {
          extInfo.version = "";
          this._logger.warn("_getExtensionsToInstall:\n" + e);
        }
        try {
          extInfo.OS = iniParser.getString(section,"OS");
        } catch (e) {
          extInfo.OS = "";
          this._logger.warn("_getExtensionsToInstall:\n" + e);
        }

        extensions.push(extInfo);
      }
    } else {
      this._logger.error("_getExtensionsToInstall: config.ini not found!");
    }

    return extensions;
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

    AppcAddi.ObserverService.notifyObservers(
      cancelQuit, "quit-application-requested", "restart");

    // something aborted the quit process.
    if (cancelQuit.data) {
      return;
    }

    restartService.quit(
      Ci.nsIAppStartup.eRestart | Ci.nsIAppStartup.eAttemptQuit);
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
      "ac-addi-loading-dialog", "chrome,centerscreen,resizable=no",
      null);

    loadingDialog.focus();
  }
};

/**
 * Constructor.
 */
(function() {
  this._init();
}).apply(AppcAddi.InstallerService);
