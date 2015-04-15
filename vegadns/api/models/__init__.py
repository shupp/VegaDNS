from vegadns.api.common import config # need logger
from peewee import *
from lib.shortcuts import model_to_dict


database = MySQLDatabase(
    config.get('mysql', 'database'),
    **{
        'host': config.get('mysql', 'host'),
        'password': config.get('mysql', 'password'),
        'user': config.get('mysql', 'user')
      }
)

class BaseModel(Model):
    def to_dict(self):
        return model_to_dict(self)
    class Meta:
        database = database
