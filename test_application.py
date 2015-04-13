import unittest
import json
from application import app


class TestDomains(unittest.TestCase):
    def test_get(self):
        # Use Flask's test client
        self.test_app = app.test_client()
        response = self.test_app.get('/v1/domains')
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domains'][0]['id'], 1)
