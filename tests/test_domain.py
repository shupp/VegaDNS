import unittest
import json
from vegadns.api import app
from vegadns.api.endpoints import domain


class TestDomain(unittest.TestCase):
    def test_get(self):
        # Use Flask's test client
        self.test_app = app.test_client()
        response = self.test_app.get('/domains/1234')
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domain']['id'], 1234)
