<?php
return ['seed_catalog'=>(bool)env('MEAL_SEED_CATALOG',false),'seed_dev_accounts'=>(bool)env('MEAL_SEED_DEV_ACCOUNTS',false),'quote_url'=>env('MEAL_QUOTE_URL'),'delivery_enabled'=>(bool)env('MEAL_DELIVERY_ENABLED',false),'delivery_fee'=>(int)env('MEAL_DELIVERY_FEE',5000)];
