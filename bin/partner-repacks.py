#!/usr/bin/env python

import os, re, sys
from shutil import copy, copytree, move
from subprocess import Popen
from optparse import OptionParser

PARTNERS_DIR = "../partners"
BUILD_NUMBER = "1"
STAGING_SERVER = "stage.mozilla.org"

#########################################################################
# Source: 
# http://stackoverflow.com/questions/377017/test-if-executable-exists-in-python
def which(program):
    def is_exe(fpath):
        return os.path.exists(fpath) and os.access(fpath, os.X_OK)

    fpath, fname = os.path.split(program)
    if fpath:
        if is_exe(program):
            return program
    else:
        for path in os.environ["PATH"].split(os.pathsep):
            exe_file = os.path.join(path, program)
            if is_exe(exe_file):
                return exe_file

    return None

#########################################################################
def rmdirRecursive(dir):
    """This is a replacement for shutil.rmtree that works better under
    windows. Thanks to Bear at the OSAF for the code.
    (Borrowed from buildbot.slave.commands)"""
    if not os.path.exists(dir):
        # This handles broken links
        if os.path.islink(dir):
            os.remove(dir)
        return

    if os.path.islink(dir):
        os.remove(dir)
        return

    # Verify the directory is read/write/execute for the current user
    os.chmod(dir, 0700)

    for name in os.listdir(dir):
        full_name = os.path.join(dir, name)
        # on Windows, if we don't have write permission we can't remove
        # the file/directory either, so turn that on
        if os.name == 'nt':
            if not os.access(full_name, os.W_OK):
                # I think this is now redundant, but I don't have an NT
                # machine to test on, so I'm going to leave it in place
                # -warner
                os.chmod(full_name, 0600)

        if os.path.isdir(full_name):
            rmdirRecursive(full_name)
        else:
            # Don't try to chmod links
            if not os.path.islink(full_name):
                os.chmod(full_name, 0700)
            os.remove(full_name)
    os.rmdir(dir)

#########################################################################
def printSeparator():
    print "##################################################"

#########################################################################
def shellCommand(cmd):
    # Shell command output gets dumped immediately to stdout, whereas 
    # print statements get buffered unless we flush them explicitly.
    sys.stdout.flush()
    p = Popen(cmd, shell=True)
    (rpid, ret) = os.waitpid(p.pid, 0)
    if ret != 0:
        ret_real = (ret & 0xFF00) >> 8
        print "Error: shellCommand had non-zero exit status: %d" % ret_real 
        print "command was: %s" % cmd
        sys.exit(ret_real)
    return True
   
#########################################################################
def mkdir(dir, mode=0777):
    if not os.path.exists(dir):
        return os.makedirs(dir, mode)
    return True
   
#########################################################################
def isLinux(platform):
    if (platform.find('linux') != -1):
        return True
    return False
#########################################################################
def isMac(platform):
    if (platform.find('mac') != -1):
        return True
    return False

#########################################################################
def isWin(platform):
    if (platform.find('win') != -1):
        return True
    return False

#########################################################################
def parseRepackConfig(file):
    config = {}
    
    config['platforms'] = []
    f= open(file, 'r')
    for line in f:
        line = line.rstrip("\n")
        [key, value] = line.split('=',2)
        value = value.strip('"')
        if key == 'dist_id':
            config['dist_id'] = value
            continue
        if key == 'locales':
            config['locales'] = value.split(' ')
            continue
        if isLinux(key) or isMac(key) or isWin(key):
            if value == 'true':
                config['platforms'].append(key)
            continue
        if key == 'migrationWizardDisabled':
            if value.lower() == 'true':
                config['migrationWizardDisabled'] = True
    return config

#########################################################################
def getFormattedPlatform(platform):
    '''Returns the platform in the format used in building package names.
    '''
    if isLinux(platform):
        return "linux-i686"
    if isMac(platform):
        return "mac"
    if isWin(platform):
        return "win32"
    return None

#########################################################################
def getFilename(version, platform, locale, file_ext):
    '''Returns the properly formatted filename based on the version string.
       File location/nomenclature changed starting with 3.5.
    '''
    version_formatted = version

    # Deal with alpha/beta releases.
    m = re.match('(\d\.\d)(a|b|rc)(\d+)', version)
    if m:
        if m.group(2) == 'b':
            greek = "Beta"
        elif m.group(2) == 'a':
            greek = "Alpha"
        else:
            greek = "RC"
        version_formatted = "%s %s %s" % (m.group(1), greek, m.group(3))
        
    if version.startswith('3.0'):
        return "firefox-%s.%s.%s.%s" % (version,
                                        locale,
                                        platform,
                                        file_ext)
    else:
        if isLinux(platform):            
            return "firefox-%s.%s" % (version,
                                      file_ext)
        if isMac(platform):
            return "Firefox %s.%s" % (version_formatted,
                                      file_ext)
        if isWin(platform):            
            return "Firefox Setup %s.%s" % (version_formatted,
                                            file_ext)
        
    return None

#########################################################################
def getLocalFilePath(version, base_dir, platform, locale):
    '''Returns the properly formatted filepath based on the version string.
       File location/nomenclature changed starting with 3.5.
    '''
    if version.startswith('3.0'):
        return "%s" % (base_dir)

    return "%s/%s/%s" % (base_dir, platform, locale)

#########################################################################
def getFileExtension(version, platform):
    if isLinux(platform):
        return "tar.bz2"
    if isMac(platform):
        return "dmg"
    if isWin(platform):
        if version.startswith('3.0'):
            return "installer.exe"
        else:
            return "exe"
    return None

#########################################################################
class RepackBase(object):
    def __init__(self, build, partner_dir, build_dir, working_dir, final_dir,
                 repack_info):
        self.base_dir = os.getcwd()
        self.build = build
        self.full_build_path = "%s/%s/%s" % (self.base_dir, build_dir, build)
        self.full_partner_path = "%s/%s" % (self.base_dir, partner_dir)
        self.working_dir = working_dir
        self.final_dir = final_dir
        self.repack_info = repack_info
        mkdir(self.working_dir)
        self.platform = None

    def announceStart(self):
        print "### Repacking %s build %s" % (self.platform, self.build)

    def unpackBuild(self):    
        copy(self.full_build_path, '.')

    def createOverrideIni(self, partner_path):
        ''' Some partners need to override the migration wizard. This is done
            by adding an override.ini file to the base install dir.
        '''
        filename='%s/override.ini' % partner_path
        if self.repack_info.has_key('migrationWizardDisabled'):
            if not os.path.isfile(filename): 
                f=open(filename,'w')
                f.write('[XRE]\n')
                f.write('EnableProfileMigrator=0\n')
                f.close()

    def copyFiles(self, platform_dir):
        # Check whether we've already copied files over for this partner.
        if not os.path.exists(platform_dir):
            mkdir(platform_dir)
            for i in ['distribution', 'extensions', 'searchplugins']:
                full_path = "%s/%s" % (self.full_partner_path, i)
                if os.path.exists(full_path):
                    copytree(full_path, "%s/%s" % (platform_dir,i)) 
            self.createOverrideIni(platform_dir)

    def repackBuild(self):
        pass
        
    def cleanup(self):
        if self.final_dir == '.':
            move(self.build, '..')
        else:
            move(self.build, "../%s" % self.final_dir)

    def doRepack(self):
        self.announceStart()
        os.chdir(self.working_dir)
        self.unpackBuild()
        self.copyFiles()
        self.repackBuild()
        self.cleanup()
        os.chdir(self.base_dir)        

#########################################################################
class RepackLinux(RepackBase):
    def __init__(self, build, partner_dir, build_dir, working_dir, final_dir,
                 repack_info):
        super(RepackLinux, self).__init__(build, partner_dir,
                                          build_dir, working_dir,
                                          final_dir, repack_info)
        self.platform = "linux"
        self.uncompressed_build = build.replace('.bz2','')

    def unpackBuild(self):
        super(RepackLinux, self).unpackBuild()
        bunzip2_cmd = "bunzip2 %s" % self.build
        shellCommand(bunzip2_cmd)
        if not os.path.exists(self.uncompressed_build):
            print "Error: Unable to uncompress build %s" % self.build
            sys.exit(1)

    def copyFiles(self):
        super(RepackLinux, self).copyFiles('firefox')

    def repackBuild(self):
        tar_cmd = "tar rvf %s firefox" % self.uncompressed_build
        shellCommand(tar_cmd)
        bzip2_command = "bzip2 %s" % self.uncompressed_build
        shellCommand(bzip2_command)

#########################################################################
class RepackMac(RepackBase):
    def __init__(self, build, partner_dir, build_dir, working_dir, final_dir,
                 repack_info):
        super(RepackMac, self).__init__(build, partner_dir,
                                        build_dir, working_dir,
                                        final_dir, repack_info)
        self.platform = "mac"
        self.mountpoint = "/tmp/FirefoxInstaller"

    def unpackBuild(self):
        mkdir(self.mountpoint)

        # Verify that Firefox isn't currently mounted on our mountpoint.
        if os.path.exists("%s/Firefox.app" % self.mountpoint):
            print "Error: Firefox is already mounted at %s" % self.mountpoint
            sys.exit(1)
    
        attach_cmd = "hdiutil attach -mountpoint %s -readonly -private -noautoopen \"%s\"" % (self.mountpoint, self.full_build_path)
        shellCommand(attach_cmd)
        rsync_cmd  = "rsync -a %s/ stage/" % self.mountpoint
        shellCommand(rsync_cmd)
        eject_cmd  = "hdiutil eject %s" % self.mountpoint
        shellCommand(eject_cmd)

        # Disk images contain a link " " to "Applications/" that we need 
        # to get rid of while working with it uncompressed. 
        os.remove("stage/ ")

    def copyFiles(self):
        for i in ['distribution', 'extensions', 'searchplugins']:
            full_path = "%s/%s" % (self.full_partner_path, i)
            if os.path.exists(full_path):
                cp_cmd = "cp -r %s stage/Firefox.app/Contents/MacOS" % full_path
                shellCommand(cp_cmd)
        self.createOverrideIni('stage/Firefox.app/Contents/MacOS')

    def repackBuild(self):
        pkg_cmd = "pkg-dmg --source stage/ --target \"%s\" --volname 'Firefox' --icon stage/.VolumeIcon.icns --symlink '/Applications':' '" % self.build
        shellCommand(pkg_cmd)

    def cleanup(self):
        super(RepackMac, self).cleanup()
        rmdirRecursive("stage")
        rmdirRecursive(self.mountpoint)

#########################################################################
class RepackWin32(RepackBase):
    def __init__(self, build, partner_dir, build_dir, working_dir, final_dir,
                 repack_info):
        super(RepackWin32, self).__init__(build, partner_dir,
                                          build_dir, working_dir,
                                          final_dir, repack_info)
        self.platform = "win32"

    def copyFiles(self):
        super(RepackWin32, self).copyFiles('nonlocalized')

    def repackBuild(self):
        zip_cmd = "7za a \"%s\" nonlocalized" % self.build
        shellCommand(zip_cmd)
 
#########################################################################
if __name__ == '__main__':
    error = False
    partner_builds = {}
    repack_build = {'linux-i686': RepackLinux,
                    'mac':        RepackMac,
                    'win32':      RepackWin32
    }

    parser = OptionParser(usage="usage: %prog [options]")
    parser.add_option("-d", 
                      "--partners-dir",
                      action="store", 
                      dest="partners_dir",
                      default=PARTNERS_DIR,
                      help="Specify the directory where the partner config files are found.")
    parser.add_option("-p",
                      "--partner",
                      action="store",
                      dest="partner",
                      help="Repack for a single partner, specified by name."
                     )
    parser.add_option("-v",
                      "--version",
                      action="store",
                      dest="version",
                      help="Set the version number for repacking")
    parser.add_option("-n",
                      "--build-number",
                      action="store",
                      dest="build_number",
                      default=BUILD_NUMBER,
                      help="Set the build number for repacking")
    parser.add_option("--verify-only",
                      action="store_true",
                      dest="verify_only",
                      default=False,
                      help="Check for existing partner repacks.")
    (options, args) = parser.parse_args()

    # Pre-flight checks
    if not options.version:
        print "Error: you must specify a version number."
        error = True

    if not os.path.isdir(options.partners_dir):
        print "Error: partners dir %s is not a directory." % partners_dir
        error = True

    # We only care about the tools if we're actually going to
    # do some repacking.
    if not options.verify_only:
        if not which("7za"):
            print "Error: couldn't find the 7za executable in PATH."
            error = True

        if not which("pkg-dmg"):
            print "Error: couldn't find the pkg-dmg executable in PATH."
            error = True

    if error:
        sys.exit(1)

    base_workdir = os.getcwd();

    # Remote dir where we can find builds.
    candidates_web_dir = "/pub/mozilla.org/firefox/nightly/%s-candidates/build%s" % (options.version, options.build_number)
 
    # Local directories for builds
    original_builds_dir = "original_builds/%s/build%s" % (options.version, str(options.build_number))
    repacked_builds_dir = "repacked_builds/%s/build%s" % (options.version, str(options.build_number))
    if not options.verify_only:
        mkdir(original_builds_dir)
        mkdir(repacked_builds_dir)
        printSeparator()

    # For each partner in the partners dir
    ##    Read/check the config file
    ##    Download required builds (if not already on disk)
    ##    Perform repacks

    for partner_dir in os.listdir(options.partners_dir):
        if options.partner:
            if options.partner != partner_dir:
                continue
        full_partner_dir = "%s/%s" % (options.partners_dir,partner_dir)
        if not os.path.isdir(full_partner_dir):
            continue
        repack_cfg = "%s/repack.cfg" % str(full_partner_dir)
        if not options.verify_only:
            print "### Starting repack process for partner: %s" % partner_dir
        else: 
            print "### Verifying existing repacks for partner: %s" % partner_dir
        if not os.path.exists(repack_cfg):
            print "### %s doesn't exist, skipping this partner" % repack_cfg
            continue
        repack_info = parseRepackConfig(repack_cfg)

        partner_repack_dir = "%s/%s" % (repacked_builds_dir, partner_dir)
        if not options.verify_only:
            if os.path.exists(partner_repack_dir):
                rmdirRecursive(partner_repack_dir)
            mkdir(partner_repack_dir)
            working_dir = "%s/working" % partner_repack_dir
            mkdir(working_dir)
 
        # Figure out which base builds we need to repack.
        for locale in repack_info['locales']:
            for platform in repack_info['platforms']:
                # ja-JP-mac only exists for Mac, so skip non-existent
                # platform/locale combos.
                if (locale == 'ja' and isMac(platform)) or \
                   (locale == 'ja-JP-mac' and not isMac(platform)):
                   continue
                platform_formatted = getFormattedPlatform(platform)
                file_ext = getFileExtension(options.version,
                                            platform_formatted);
                filename = getFilename(options.version,
                                       platform_formatted,
                                       locale,
                                       file_ext)
                local_filepath = getLocalFilePath(options.version,
                                                  original_builds_dir,
                                                  platform_formatted,
                                                  locale
                                                 )
                if not options.verify_only:
                    mkdir(local_filepath)
                local_filename = "%s/%s" % (local_filepath, filename)
                if options.version.startswith('3.0'):
                    final_dir = '.'
                else:
                    final_dir = "%s/%s" % (platform_formatted,
                                           locale
                                          )
                    if not options.verify_only:
                        mkdir("%s/%s" % (partner_repack_dir, final_dir))

                # Check to see if this build is already on disk, i.e.
                # has already been downloaded.
                if not options.verify_only:
                    if os.path.exists(local_filename):
                        print "### Found %s on disk, not downloading" % filename
                    else:
                        # Download original build from stage
                        os.chdir(local_filepath)
                        if options.version.startswith('3.0'):
                            original_build_url = "http://%s%s/%s" % \
                                                 (STAGING_SERVER,
                                                  candidates_web_dir,
                                                  filename
                                                 )
                        else:
                            original_build_url = "http://%s%s/%s/%s/%s" % \
                                                 (STAGING_SERVER,
                                                  candidates_web_dir,
                                                  platform_formatted,
                                                  locale,
                                                  filename
                                                 )
                            
                        wget_cmd = "wget -q \"%s\"" % original_build_url
                        shellCommand(wget_cmd)
                        os.chdir(base_workdir);
                         
                    # Make sure we have the local file now               
                    if not os.path.exists(local_filename):
                        print "Error: Unable to retrieve %s" % filename
                        sys.exit(1)

                    repackObj = repack_build[platform_formatted](filename, 
                                                                 full_partner_dir,
                                                                 local_filepath,
                                                                 working_dir,
                                                                 final_dir,
                                                                 repack_info)
                    repackObj.doRepack()
                else:
                    repacked_build = "%s/%s/%s" % (partner_repack_dir, final_dir, filename)
                    if not os.path.exists(repacked_build):
                        print "Error: missing expected repack for partner %s (%s/%s): %s" % (partner_dir, platform_formatted, locale, filename)
                        error = True
        
        if not options.verify_only:
            # Remove our working dir so things are all cleaned up and ready for
            # easy upload.
            rmdirRecursive(working_dir)
            printSeparator()

    if error:
        sys.exit(1)

