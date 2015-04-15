from flask import Flask, abort, redirect, url_for
from flask.ext.restful import Resource, Api, abort
from vegadns.api import endpoint
from vegadns.api.models.record import Record as ModelRecord

@endpoint
class Record(Resource):
    route = '/records/<int:id>'

    def get(self, id):
        try:
            record = self.get_record(id)
        except:
            abort(404, message="record does not exist")
        return {'status': 'ok', 'record': record.to_dict()}

    def get_record(self, id):
        # FIXME authorization
        return ModelRecord.get(ModelRecord.record_id == id)
