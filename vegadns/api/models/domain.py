from peewee import *
from vegadns.api.models import database, BaseModel

class Domain(BaseModel):
    domain = CharField()
    domain_id = IntegerField(primary_key=True)
    group_owner = IntegerField(db_column='group_owner_id', null=True)
    owner = IntegerField(db_column='owner_id', null=True)
    status = CharField()

    class Meta:
        db_table = 'domains'
