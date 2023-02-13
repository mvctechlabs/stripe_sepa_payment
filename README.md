# stripe_sepa_payment

# run composer update command to get required libraries
# change your stripe API key, ibancalculator.com api URI, Stripe Product key in includes/constants.php
# Upload excel file with Iban number and name example excel file in uploads/ folder
# Customer banks address and country fetched from ibancalculator.com
# creates a source in stripe with the IBAN number to automatic payment
# create a new customer with the source Id
# then add the subscription to the customer
