import json
import unittest

from mock import MagicMock

import vegadns.api.endpoints.records
from vegadns.api import app


class TestRecords(unittest.TestCase):
    def setUp(self):
        # Use Flask's test client
        self.test_app = app.test_client()

    def test_get_success(self):
        mock_record_one = {
            "distance": 0,
            "domain_id": 1,
            "host": "1.b.vegadns.ubuntu",
            "port": None,
            "record_id": 8,
            "ttl": 3600,
            "type": "A",
            "val": "1.2.3.4",
            "weight": None
        }
        mock_model_one = MagicMock()
        mock_model_one.to_dict = MagicMock(return_value=mock_record_one)

        mock_record_two = {
            "distance": 0,
            "domain_id": 1,
            "host": "1.c.vegadns.ubuntu",
            "port": None,
            "record_id": 9,
            "ttl": 3600,
            "type": "A",
            "val": "1.2.3.4",
            "weight": None
        }
        mock_model_two = MagicMock()
        mock_model_two.to_dict = MagicMock(return_value=mock_record_two)

        vegadns.api.endpoints.records.Records.get_record_list = MagicMock(
            return_value=[mock_model_one, mock_model_two]
        )

        response = self.test_app.get('/records?domain=1')
        self.assertEqual(response.status, "200 OK")

        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['records'][0]['record_id'], 8)
        self.assertEqual(decoded['records'][1]['record_id'], 9)
