import os
import logging
import logging.handlers
import socket

HOST_NAME = socket.gethostname()
DIR_NAME_BY_HOST = {'DNRAICDEN0188': 'http://localhost:8000/', # Name of localhost - change this depends on your local development enviroment
                    'caic1.localdomain': 'http://caic.mtnweather.info/SnowpackViewerTool/',
                    'linode1': 'https://linode1.snowpilot.org/realtime-tools/'}
HOST_PARENT_DIR = DIR_NAME_BY_HOST[HOST_NAME]
if HOST_NAME=='DNRAICDEN0188': # Name of localhost - change this depends on your local development enviroment
    PARENT_DIR = os.path.abspath(os.path.dirname(os.getcwd()))
    LOG_DIR = os.path.join(PARENT_DIR, 'LOGS')
    PITS_PATH = os.path.join(PARENT_DIR, 'data', 'snowpits')
    GEODATA_PATH = os.path.join(PARENT_DIR, 'data', 'geodata')
    AVALANCHES_PATH = os.path.join(PARENT_DIR, 'data', 'avalanches')
if HOST_NAME=='caic1.localdomain':
    PARENT_DIR = '/home/www/html/SnowpackViewerTool'
    LOG_DIR = os.path.join('/var/www/SnowpackViewerTool', 'LOGS')
    PITS_PATH = os.path.join('/var/www/SnowpackViewerTool', 'data', 'snowpits')
    GEODATA_PATH = os.path.join('/var/www/SnowpackViewerTool', 'data', 'geodata')
    AVALANCHES_PATH = os.path.join('/var/www/SnowpackViewerTool', 'data', 'avalanches')
if HOST_NAME=='linode1':
    PARENT_DIR = '/var/www/html/linode1.snowpilot.org/public_html/realtime-tools/'
    SRC_DIR = '/home/ron/scripts/realtime-tools/'
    LOG_DIR = os.path.join(SRC_DIR, 'LOGS')
    PITS_PATH = os.path.join(SRC_DIR, 'data', 'snowpits')
    GEODATA_PATH = os.path.join(SRC_DIR, 'data', 'geodata')
    AVALANCHES_PATH = os.path.join(SRC_DIR, 'data', 'avalanches')

    
    
    
DEFAULT_MAP = '_base_pit_map.html'
LEGEND_PATH = os.path.join('data', 'legends')
MAPS_PATH = os.path.join('data', 'maps')
os.environ['LOG_DIR'] = LOG_DIR

def set_logger(name=__name__, 
               log_file=os.environ.get('LOGFILE', os.path.join(LOG_DIR, 'SnowPilotOnMap.log')),
               level=os.environ.get('LOGLEVEL', 'INFO')):
    if not os.path.isdir(LOG_DIR):
        os.makedirs(LOG_DIR, exist_ok=True)
    log = logging.getLogger(name)
    handler = logging.handlers.WatchedFileHandler(log_file)
    formatter = logging.Formatter('[%(asctime)s] p%(process)s {%(pathname)s:%(lineno)d} %(levelname)s - %(message)s','%Y-%m-%d %H:%M:%S')
    handler.setFormatter(formatter)
    log.setLevel(os.environ.get('LOGLEVEL', 'INFO'))
    log.addHandler(handler)
    
    return log
    
      
with open(os.path.join(PARENT_DIR, 'src', '.env'), 'r') as cf:
    conf = cf.readlines()
    
for env_param in conf:
    key, val = [x.strip() for x in env_param.split('=')]
    os.environ[key] = val
  