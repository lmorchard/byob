/**
 * Copyright (c) 2010 Appcoast Ltd. All rights reserved.
 */

// global preference to handle if the process is already finished.
pref("extensions.addoninstaller.installation.completed", false);
// max number of restart to retry in case of errors.
pref("extensions.addoninstaller.retry.maxRestarts", 2);
// current number of restart to retry in case of errors.
pref("extensions.addoninstaller.retry.curRestarts", 0);
