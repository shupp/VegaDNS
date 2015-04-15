from peewee import *
from vegadns.api.models import database, BaseModel


class Record(BaseModel):
    distance = IntegerField(null=True)
    domain_id = IntegerField(db_column='domain_id')
    host = CharField()
    port = IntegerField(null=True)
    record_id = IntegerField(
        db_column='record_id',
        unique=True,
        primary_key=True
    )
    ttl = IntegerField()
    type = CharField(null=True)
    val = CharField(null=True)
    weight = IntegerField(null=True)

    class Meta:
        db_table = 'records'
