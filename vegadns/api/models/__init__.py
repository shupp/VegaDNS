from vegadns.api.common import config # need logger
from peewee import *


database = MySQLDatabase(
    config.get('mysql', 'database'),
    **{
        'host': config.get('mysql', 'host'),
        'password': config.get('mysql', 'password'),
        'user': config.get('mysql', 'user')
      }
)

class BaseModel(Model):
    # http://stackoverflow.com/questions/21975920/peewee-model-to-json
    def __str__(self):
        r = {}
        for k in self._data.keys():
            try:
                r[k] = str(getattr(self, k))
            except:
                # FIXME do something better here
                r[k] = json.dumps(getattr(self, k))
        return str(r)

    class Meta:
        database = database
