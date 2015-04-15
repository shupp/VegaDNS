import unittest
import json
from mock import MagicMock

from vegadns.api import app
import vegadns.api.endpoints.record


class TestRecord(unittest.TestCase):
    def test_get_success(self):
        # mock get_record and to_dict
        mock_value = {
            "distance": 0,
            "domain_id": 2,
            "host": "hostmaster.test.com:ns1.vegadns.ubuntu",
            "port": None,
            "record_id": 10,
            "ttl": 86400,
            "type": "S",
            "val": "16384:2048:1048576:2560",
            "weight": None
        }

        mock_model = MagicMock()
        mock_model.to_dict = MagicMock(return_value = mock_value)
        vegadns.api.endpoints.record.Record.get_record = MagicMock(
            return_value = mock_model
        )

        # Use Flask's test client
        self.test_app = app.test_client()
        response = self.test_app.get('/records/10')
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['record']['record_id'], 10)
