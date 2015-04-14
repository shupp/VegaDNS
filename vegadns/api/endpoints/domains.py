from flask import Flask, abort, redirect, url_for, jsonify
from flask.ext.restful import Resource, Api
from vegadns.api import endpoint

@endpoint
class Domains(Resource):
    route = '/domains'

    def get(self):
        domain_list = [{'id': 1, 'name': 'vegadns.org', 'owner_id': 0}]
        return jsonify(status = 'ok', domains = domain_list)
