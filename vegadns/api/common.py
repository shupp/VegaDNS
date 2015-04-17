import os
from ConfigParser import SafeConfigParser

# Get config
config = SafeConfigParser()
config.read('vegadns/api/config/default.ini')
config.read('vegadns/api/config/local.ini')
