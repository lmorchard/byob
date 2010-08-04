#!/usr/bin/env python

import os, re, sys
from shutil import copy, copytree, move
import subprocess
from subprocess import Popen
from optparse import OptionParser
import urllib

# Set default values.
PARTNERS_DIR = '../partners'
BUILD_NUMBER = '1'
STAGING_SERVER = 'stage.mozilla.org'
HGROOT = 'http://hg.mozilla.org'
REPO = 'releases/mozilla-1.9.2'

PKG_DMG = 'pkg-dmg'
SEVENZIP_BIN = '7za'
UPX_BIN = 'upx'

SBOX_HOME = '/scratchbox/users/cltbld/home/cltbld/'
SBOX_PATH = '/scratchbox/moz_scratchbox'

SEVENZIP_BUNDLE = 'app.7z'
SEVENZIP_APPTAG = 'app.tag'
SEVENZIP_APPTAG_PATH = os.path.join('browser/installer/windows', SEVENZIP_APPTAG)
SEVENZIP_HEADER = '7zSD.sfx'
SEVENZIP_HEADER_PATH = os.path.join('other-licenses/7zstub/firefox', SEVENZIP_HEADER)
SEVENZIP_HEADER_COMPRESSED = SEVENZIP_HEADER + '.compressed'

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
def isMaemo(platform):
    if (platform.find('maemo') != -1):
        return True
    return False

#########################################################################
def createTagFromVersion(version):
    return 'FIREFOX_' + str(version).replace('.','_') + '_RELEASE'

#########################################################################
def parseRepackConfig(file, platforms):
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
        if isLinux(key) or isMac(key) or isWin(key) or isMaemo(key):
            if key in platforms and value == 'true':
                config['platforms'].append(key)
            continue
        if key == 'migrationWizardDisabled':
            if value.lower() == 'true':
                config['migrationWizardDisabled'] = True
            continue
        if key == 'deb_section':
            config['deb_section'] = re.sub('/', '\/', value)
    if config['platforms']:
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
    if isMaemo(platform):
        return platform
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

        if isMaemo(platform):
            deb_name_url = "http://%s%s/%s/%s/deb_name.txt" % \
                           (options.staging_server,
                            candidates_web_dir,
                            platform_formatted,
                            locale)
            filename = re.sub('\n', '', Popen(['curl', deb_name_url],
                             stdout=subprocess.PIPE).communicate()[0])
            return filename

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
        pkg_cmd = "%s --source stage/ --target \"%s\" --volname 'Firefox' --icon stage/.VolumeIcon.icns --symlink '/Applications':' '" % (options.pkg_dmg,
                                                           self.build)
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
        zip_cmd = "%s a \"%s\" nonlocalized" % (SEVENZIP_BIN, self.build)
        shellCommand(zip_cmd)

#########################################################################
class RepackMaemo(RepackBase):
    def __init__(self, build, partner_dir, build_dir, working_dir, final_dir,
                 repack_info, sbox_path=SBOX_PATH, sbox_home=SBOX_HOME):
        super(RepackMaemo, self).__init__(build, partner_dir,
                                          build_dir, working_dir,
                                          final_dir, repack_info)
        self.sbox_path = sbox_path
        self.sbox_home = sbox_home
        self.tmpdir = "%s/tmp_deb" % self.base_dir
        self.platform = platform_formatted

    def unpackBuild(self):
        mkdir("%s/DEBIAN" % self.tmpdir)
        super(RepackMaemo, self).unpackBuild()
        commandList = [
         'ar -p %s data.tar.gz | tar -zx -C %s' % (self.build, self.tmpdir),
         'ar -p %s control.tar.gz | tar -zx -C %s/DEBIAN' % (self.build,
                                                             self.tmpdir)
        ]
        for command in commandList:
            status = shellCommand(command)
            if not status:
                print "Error while running '%s'." % command
                sys.exit(status)

    def copyFiles(self):
        full_path = "%s/preferences" % self.full_partner_path
        if os.path.exists(full_path):
            cp_cmd = "cp %s/* %s/opt/mozilla/[a-z\-\.0-9]*/defaults/pref/" % \
                (full_path, self.tmpdir)
            shellCommand(cp_cmd)

    def mungeControl(self):
        print self.repack_info
        if 'deb_section' in self.repack_info:
            munge_cmd="sed -i -e 's/^Section: .*$/Section: %s/' %s/DEBIAN/control" % (self.repack_info['deb_section'], self.tmpdir)
            print munge_cmd
            shellCommand(munge_cmd)

    def repackBuild(self):
        rel_base_dir = re.sub('^.*%s' % self.sbox_home, '', self.base_dir)
        repack_cmd = '%s -p -d %s "dpkg-deb -b tmp_deb %s"' % (self.sbox_path,
                                                           rel_base_dir,
                                                           self.build)
        print repack_cmd
        shellCommand(repack_cmd)
        print self.build

    def cleanup(self):
        print self.final_dir
        move(os.path.join(self.base_dir, self.build), "../%s" % self.final_dir)
        rmdirRecursive(self.tmpdir)

    def doRepack(self):
        self.announceStart()
        os.chdir(self.working_dir)
        self.unpackBuild()
        self.copyFiles()
        self.mungeControl()
        self.repackBuild()
        self.cleanup()
        os.chdir(self.base_dir)

#########################################################################
def repackSignedBuilds(repack_dir):
    if not os.path.isdir(repack_dir):
        return False
    base_dir = os.getcwd()

    if not os.path.exists(SEVENZIP_APPTAG):
        if not getSingleFileFromHg(SEVENZIP_APPTAG_PATH):
            print "Error: Unable to retrieve %s" % SEVENZIP_APPTAG
            sys.exit(1)
    if not os.path.exists(SEVENZIP_HEADER_COMPRESSED):
        if not os.path.exists(SEVENZIP_HEADER) and \
           not getSingleFileFromHg(SEVENZIP_HEADER_PATH):
            print "Error: Unable to retrieve %s" % SEVENZIP_HEADER
            sys.exit(1)
        upx_cmd = '%s --best -o \"%s\" \"%s\"' % (UPX_BIN,
                                                  SEVENZIP_HEADER_COMPRESSED,
                                                  SEVENZIP_HEADER)
        shellCommand(upx_cmd)
        if not os.path.exists(SEVENZIP_HEADER_COMPRESSED):
            print "Error: Unable to compress %s" % SEVENZIP_HEADER
            sys.exit(1)

    for f in [SEVENZIP_HEADER_COMPRESSED, SEVENZIP_APPTAG, 'repack-signed.sh']:
        copy(f, repack_dir)
    
    os.chdir(repack_dir)
    print "Running repack.sh"
    shellCommand('./repack-signed.sh')
    for f in [SEVENZIP_HEADER_COMPRESSED, SEVENZIP_APPTAG, 'repack-signed.sh']:
        os.remove(f)
    os.chdir(base_dir)

#########################################################################
def retrieveFile(url, file_path):
  failedDownload = False
  try:
    urllib.urlretrieve(url.replace(' ','%20'), file_path)
  except:
    print "exception: n  %s, n  %s, n  %s n  when downloading %s" % \
          (sys.exc_info()[0], sys.exc_info()[1], sys.exc_info()[2], url)
    failedDownload = True

  # remove potentially only partially downloaded file, 
  if failedDownload:
    if os.path.exists(file_path):
      try:
        os.remove(file_path)
      except:
        print "exception: n  %s, n  %s, n  %s n  when trying to remove file %s" %\
              (sys.exc_info()[0], sys.exc_info()[1], sys.exc_info()[2], file_path)
    sys.exit(1)

  return True

#########################################################################
def getSingleFileFromHg(file):
    file_path = os.path.basename(file)
    url = os.path.join(options.hgroot, options.repo, 
                       'raw-file', options.tag, file)
    return retrieveFile(url, file_path)
        
#########################################################################
if __name__ == '__main__':
    error = False
    partner_builds = {}
    default_platforms = ['linux-i686', 'mac', 'win32']
    repack_build = {'linux-i686': RepackLinux,
                    'mac':        RepackMac,
                    'win32':      RepackWin32,
                    'maemo4':     RepackMaemo,
                    'maemo5-gtk': RepackMaemo
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
    parser.add_option("--platform",
                     action="append",
                     dest="platforms",
                     help="Specify platform (multiples ok)."
                     )
    parser.add_option("--nightly-dir",
                     action="store",
                     dest="nightly_dir",
                     default="firefox/nightly",
                     help="Specify the subdirectory where candidates live (default firefox/nightly)."
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
    parser.add_option("",
                      "--signed",
                      action="store_true",
                      dest="use_signed",
                      default=False,
                      help="Use Windows builds that have already been signed")
    parser.add_option("",
                      "--hgroot",
                      action="store",
                      dest="hgroot",
                      default=HGROOT,
                      help="Set the root URL for retrieving files from hg")
    parser.add_option("-r",
                      "--repo",
                      action="store",
                      dest="repo",
                      default=REPO,
                      help="Set the release tag used for retrieving files from hg")
    parser.add_option("-t",
                      "--tag",
                      action="store",
                      dest="tag",
                      help="Set the release tag used for retrieving files from hg")
    parser.add_option("",
                      "--pkg-dmg",
                      action="store",
                      dest="pkg_dmg",
                      default=PKG_DMG,
                      help="Set the path to the pkg-dmg for Mac packaging")
    parser.add_option("",
                      "--staging-server",
                      action="store",
                      dest="staging_server",
                      default=STAGING_SERVER,
                      help="Set the staging server to use for downloading/uploading.")
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

    if not options.tag:
        options.tag = createTagFromVersion(options.version)
        if not options.tag:
          print "Error: you must specify a release tag for hg."
          error = True

    if not os.path.isdir(options.partners_dir):
        print "Error: partners dir %s is not a directory." % partners_dir
        error = True

    if not options.platforms:
        options.platforms = default_platforms

    # We only care about the tools if we're actually going to
    # do some repacking.
    if not options.verify_only:
        if "win32" in options.platforms and not which(SEVENZIP_BIN):
            print "Error: couldn't find the %s executable in PATH." % SEVENZIP_BIN
            error = True

        if "win32" in options.platforms and \
           options.use_signed and \
           not which(UPX_BIN):
            print "Error: couldn't find the %s executable in PATH." % UPX_BIN
            error = True

        if "mac" in options.platforms and not which(options.pkg_dmg):
            print "Error: couldn't find the pkg-dmg executable in PATH."
            error = True

    if error:
        sys.exit(1)

    base_workdir = os.getcwd();

    # Remote dir where we can find builds.
    candidates_web_dir = "/pub/mozilla.org/%s/%s-candidates/build%s" % (options.nightly_dir, options.version, options.build_number)
    if options.use_signed:
        win32_candidates_web_dir = candidates_web_dir
    else:
        win32_candidates_web_dir = candidates_web_dir + '/unsigned'


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
        repack_info = parseRepackConfig(repack_cfg, options.platforms)
        if not repack_info:
            continue

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
                                            platform_formatted)
                filename = getFilename(options.version,
                                       platform_formatted,
                                       locale,
                                       file_ext)

                local_filepath = getLocalFilePath(options.version,
                                                  original_builds_dir,
                                                  platform_formatted,
                                                  locale)
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
                        if isWin(platform):
                            candidates_dir = win32_candidates_web_dir
                        else:
                            candidates_dir = candidates_web_dir
                        if options.version.startswith('3.0'):
                            original_build_url = "http://%s%s/%s" % \
                                                 (options.staging_server,
                                                  candidates_dir,
                                                  filename
                                                 )
                        else:
                            original_build_url = "http://%s%s/%s/%s/%s" % \
                                                 (options.staging_server,
                                                  candidates_dir,
                                                  platform_formatted,
                                                  locale,
                                                  filename
                                                 )

                        retrieveFile(original_build_url, filename)
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
            # Check to see whether we repacked any signed Windows builds. If we
            # did we need to do some scrubbing before we upload them for
            # re-signing.
            if 'win32' in repack_info['platforms'] and options.use_signed:
                repackSignedBuilds(repacked_builds_dir)
            # Remove our working dir so things are all cleaned up and ready for
            # easy upload.
            rmdirRecursive(working_dir)
            printSeparator()

    if error:
        sys.exit(1)
