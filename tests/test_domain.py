import unittest
import json
from mock import MagicMock

from vegadns.api import app
import vegadns.api.endpoints.domain


class TestDomain(unittest.TestCase):
    def test_get_success(self):
        # mock get_domain and to_dict
        mock_value = {
            'owner': 0,
            'status': 'active',
            'group_owner': 0,
            'domain': 'vegadns.org',
            'domain_id': 1
        }
        mock_model = MagicMock()
        mock_model.to_dict = MagicMock(return_value = mock_value)
        vegadns.api.endpoints.domain.Domain.get_domain = MagicMock(
            return_value = mock_model
        )

        # Use Flask's test client
        self.test_app = app.test_client()
        response = self.test_app.get('/domains/1')
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domain']['domain_id'], 1)
