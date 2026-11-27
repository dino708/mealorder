"""AWS Lambda Python handler. Estimates only; checkout recalculates prices."""
from decimal import Decimal, InvalidOperation
import json

def lambda_handler(event, context):
    try:
        data = json.loads(event["body"]) if isinstance(event.get("body"), str) else event
        items = data["items"]
        if not isinstance(items, list) or not 1 <= len(items) <= 50:
            raise ValueError("Invalid items")
        total = Decimal("0")
        for item in items:
            price = Decimal(str(item["price"]))
            qty = item["quantity"]
            if not price.is_finite() or not 0 <= price <= Decimal("9999999999.99") or price != price.quantize(Decimal(".01")) or type(qty) is not int or not 1 <= qty <= 100:
                raise ValueError("Invalid price or quantity")
            total += price * qty
        return {"statusCode": 200, "body": json.dumps({"estimate": str(total.quantize(Decimal(".01"))), "authoritative": False})}
    except (ValueError, KeyError, TypeError, InvalidOperation, AttributeError):
        return {"statusCode": 422, "body": json.dumps({"error": "Invalid request"})}
