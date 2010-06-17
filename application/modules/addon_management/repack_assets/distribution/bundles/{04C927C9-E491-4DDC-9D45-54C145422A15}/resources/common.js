/**
 * Copyright (c) 2010 Appcoast Ltd. All rights reserved.
 */

var EXPORTED_SYMBOLS = ["AddonInstaller"];

const Cc = Components.classes;
const Ci = Components.interfaces;
const Cu = Components.utils;

Cu.import("resource://addoninstaller/log4moz.js");

/**
 * AddonInstaller namespace.
 */
if ("undefined" == typeof(AddonInstaller)) {
  AddonInstaller = {
    /* The FUEL Application object. */
    _application : null,
    /* Reference to the observer service. */
    _observerService : null,
    /* Reference to the directory service. */
    _directoryService : null,

    /**
     * Initialize this object.
     */
    _init : function() {
      this._observerService =
        Cc["@mozilla.org/observer-service;1"].getService(Ci.nsIObserverService);
      this._directoryService =
        Cc["@mozilla.org/file/directory_service;1"].
          getService(Ci.nsIProperties);

      // The basic formatter will output lines like:
      // DATE/TIME  LoggerName LEVEL  (log message)
      let formatter = new Log4Moz.AppcoastFormatter();
      let root = Log4Moz.repository.rootLogger;
      let logFile = this.getProfileDirectory();
      let app;

      logFile.append("log.txt");

      // Loggers are hierarchical, lowering this log level will affect all
      // output.
      root.level = Log4Moz.Level["All"];

      // A console appender outputs to the JS Error Console.
      // app = new Log4Moz.ConsoleAppender(formatter);
      // app.level = Log4Moz.Level["All"];
      // root.addAppender(app);

      // A dump appender outputs to standard out.
      //app = new Log4Moz.DumpAppender(formatter);
      //app.level = Log4Moz.Level["Warn"];
      //root.addAppender(app);

      // This appender will log to the file system.
      app = new Log4Moz.RotatingFileAppender(logFile, formatter);
      app.level = Log4Moz.Level["Warn"];
      root.addAppender(app);
    },

    /**
     * Gets a logger repository from Log4Moz.
     * @param aName the name of the logger to create.
     * @param aLevel (optional) the logger level.
     * @return the generated logger.
     */
    getLogger : function(aName, aLevel) {
      let logger = Log4Moz.repository.getLogger(aName);

      logger.level = Log4Moz.Level[(aLevel ? aLevel : "All")];

      return logger;
    },

    /**
     * Gets the FUEL Application object.
     * @return the FUEL Application object.
     */
    get Application() {
      // use lazy initialization because the FUEL object is only available for
      // Firefox and won't work on XUL Runner builds.

      if (null == this._application) {
        try {
          this._application =
            Cc["@mozilla.org/fuel/application;1"].
              getService(Ci.fuelIApplication);
        } catch (e) {
          throw "The FUEL application object is not available.";
        }
      }

      return this._application;
    },

    /**
     * Gets the preference branch.
     * @return the preference branch.
     */
    get PrefBranch() { return "extensions.addoninstaller."; },

    /**
     * Gets the id of this extension.
     * @return the id of this extension.
     */
    get ExtensionId() { return "{04C927C9-E491-4DDC-9D45-54C145422A15}"; },

    /**
     * Gets the observer service.
     * @return the observer service.
     */
    get ObserverService() { return this._observerService; },

    /**
     * Gets a reference to the directory where the extension will keep its
     * files. The directory is created if it doesn't exist.
     * @return reference (nsIFile) to the profile directory.
     */
    getProfileDirectory : function() {
      // XXX: there's no logging here because the logger initialization depends
      // on this method.

      let profDir = this._directoryService.get("ProfD", Ci.nsIFile);

      profDir.append("AddonInstaller");

      if (!profDir.exists() || !profDir.isDirectory()) {
        // read and write permissions to owner and group, read-only for others.
        profDir.create(Ci.nsIFile.DIRECTORY_TYPE, 0774);
      }

      return profDir;
    },

    /**
     * Gets a reference to the directory where are the extension to be
     * installed. The directory is created if it doesn't exist.
     * @return reference (nsIFile) to the extension directory.
     */
    getExtensionsDirectory : function() {
      // XXX: there's no logging here because the logger initialization depends
      // on this method.

      let extensionsDir =
        this._directoryService.get("resource:app", Ci.nsIFile);

      extensionsDir.append("distribution");
      extensionsDir.append("extensions");

      if (!extensionsDir.exists() || !extensionsDir.isDirectory()) {
        // read and write permissions to owner and group, read-only for others.
        extensionsDir.create(Ci.nsIFile.DIRECTORY_TYPE, 0774);
      }

      return extensionsDir;
    }
  };

  /**
   * Constructor.
   */
  (function() {
    this._init();
  }).apply(AddonInstaller);
}
