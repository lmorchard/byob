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
