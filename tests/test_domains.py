import unittest
import json

from mock import MagicMock

import vegadns.api.endpoints.domains
from vegadns.api import app


class TestDomains(unittest.TestCase):
    def setUp(self):
        # Use Flask's test client
        self.test_app = app.test_client()

    def test_get_success(self):
        mock_domain_one = {
            'owner': 0,
            'status': 'active',
            'group_owner': 0,
            'domain': 'vegadns.org',
            'domain_id': 1
        }
        mock_model_one = MagicMock()
        mock_model_one.to_dict = MagicMock(return_value=mock_domain_one)

        mock_domain_two = {
            'owner': 0,
            'status': 'active',
            'group_owner': 0,
            'domain': 'vegadns.net',
            'domain_id': 2
        }
        mock_model_two = MagicMock()
        mock_model_two.to_dict = MagicMock(return_value=mock_domain_two)

        vegadns.api.endpoints.domains.Domains.get_domain_list = MagicMock(
            return_value=[mock_model_one, mock_model_two]
        )

        response = self.test_app.get('/domains')
        self.assertEqual(response.status, "200 OK")

        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domains'][0]['domain_id'], 1)
        self.assertEqual(decoded['domains'][0]['domain'], 'vegadns.org')
        self.assertEqual(decoded['domains'][0]['owner'], 0)
        self.assertEqual(decoded['domains'][1]['domain_id'], 2)
        self.assertEqual(decoded['domains'][1]['domain'], 'vegadns.net')
        self.assertEqual(decoded['domains'][1]['owner'], 0)
