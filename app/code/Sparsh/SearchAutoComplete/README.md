##Sparsh Ajax Search Autocomplete Tracking Extension
Ajax Search Autocomplete extension improves customer's search experience by providing instant results and makes it simple to find exactly what they need, quickly and conveniently from products, categories, CMS pages and provide search suggestions.

provides  and .

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the files
3. Create a folder [Magento_Root]/app/code/Sparsh/SearchAutoComplete
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/SearchAutoComplete'

#Enable Extension:
- php bin/magento module:enable Sparsh_SearchAutoComplete
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_SearchAutoComplete
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
