from flask import Flask, abort, redirect, url_for
from flask.ext.restful import Resource, Api, abort

from vegadns.api import endpoint
from vegadns.api.models.domain import Domain as ModelDomain


@endpoint
class Domain(Resource):
    route = '/domains/<int:domain_id>'

    def get(self, domain_id):
        try:
            domain = self.get_domain(domain_id)
        except:
            abort(404, message="domain does not exist")
        return {'status': 'ok', 'domain': domain.to_dict()}

    def get_domain(self, domain_id):
        # FIXME authorization
        return ModelDomain.get(ModelDomain.domain_id == domain_id)
