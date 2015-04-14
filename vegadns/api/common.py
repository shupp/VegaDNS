from ConfigParser import SafeConfigParser
import os

# Get config
config = SafeConfigParser()
config.read('vegadns/api/config/default.ini')
if (os.access('vegadns/api/config/local.ini', os.R_OK)):
    config.read('vegadns/api/config/local.ini')
