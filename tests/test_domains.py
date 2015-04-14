import unittest
import json
from vegadns.api import app
from vegadns.api.endpoints import domains


class TestDomains(unittest.TestCase):
    def test_get(self):
        # Use Flask's test client
        self.test_app = app.test_client()
        response = self.test_app.get('/domains')
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domains'][0]['id'], 1)
        self.assertEqual(decoded['domains'][0]['name'], 'vegadns.org')
        self.assertEqual(decoded['domains'][0]['owner_id'], 0)
