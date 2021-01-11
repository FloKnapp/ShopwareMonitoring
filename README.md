# Shopware 6 Application Monitoring

### Introduction

This plugin enables your shopware installation to track the actual 
application process time. The gathered data is getting pushed to 
NewRelic through their MetricsAPI.

### Requirements

You need to obtain your own API Key at [NewRelic Insights](https://insights.eu.newrelic.com/).

You have to insert the API Key in the corresponding 
configuration key ```new_relic.api_key``` within the ```services.yaml```.
