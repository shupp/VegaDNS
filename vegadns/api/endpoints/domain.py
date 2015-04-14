from flask import Flask, abort, redirect, url_for, jsonify
from flask.ext.restful import Resource, Api
from vegadns.api import endpoint

@endpoint
class Domain(Resource):
    route = '/domains/<int:id>'

    def get(self, id):
        return jsonify(status = 'ok', domain = {'id': id})
