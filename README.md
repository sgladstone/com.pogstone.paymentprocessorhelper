com.pogstone.paymentprocessorhelper
===================================

Do NOT use this extension. It is obsolete. Also there have been numerous updates and improvements to CiviCRM core that render this extension unneeded. 


The basic structure of this CiviCRM native extension:

1) Intercept all messages/notifications/data from a payment processor and immediately insert it into a database table. (Do not attempt to make sense of it and/or create a contribution, etc) This bypasses the core logic of trying to create a contribution immediatly when an IPN notification is received. (MySQL tables created by this extension: "pogstone_authnet_messages" and "pogstone_paypal_messages")

2) This extension creates a new scheduled job named "Process data from payment processors" that is set to run every hour that will query that database table from step 1 looking for data that has not been processed yet. For any new data that represents a successful transaction, it will create a new contribution. (This includes handling multiple line items, custom data, campaign ID, etc so that all the new contributions match the first contribution in the recurring schedule)    It also handles some minor housekeeping like marking the first "pending" contribution as cancelled if the user cancels the subscription before the first installment.


This extension also provides a custom search that queries the database tables from step 1. (there is a different table for Authorize.net, PayPal, and eWAY) 

Future plans: When there is a native extension for Authorize.net and PayPal, the creation of tables "pogstone_authnet_messages" and "pogstone_paypal_messages" should move to those extensions.  Also create "failed" contributions for messages that indicate a failed/voided transaction. 
