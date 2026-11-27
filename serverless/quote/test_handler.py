import json
import unittest
from handler import lambda_handler
class QuoteTest(unittest.TestCase):
    def test_total(self):
        r=lambda_handler({"items":[{"price":"15000.25","quantity":2}]},None)
        self.assertEqual(json.loads(r["body"])["estimate"],"30000.50")
    def test_negative(self):
        self.assertEqual(lambda_handler({"items":[{"price":-1,"quantity":2}]},None)["statusCode"],422)
if __name__=="__main__": unittest.main()
