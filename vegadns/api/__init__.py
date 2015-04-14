from flask import Flask, abort, redirect, url_for, jsonify
from flask.ext.restful import Resource, Api


app = Flask(__name__)
api = Api(app)


def endpoint(cls):
    """A shorthand decorator to create an endpoint out of a class."""
    cls_route = getattr(cls, 'route', None)

    if cls_route is None:
        raise Exception('A class field "route" is required')

    api.add_resource(cls, cls_route)
    return cls
