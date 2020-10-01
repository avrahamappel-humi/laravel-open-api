# Laravel OpenApi generator

This package provides painless OpenApi YAML generation from existing routes. 

// Still in dev, expect issues :) //

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider.

``composer require asseco-voice/laravel-open-api``

## Usage

Running the command ``php artisan voice:open-api`` will generate a new YAML
file at ``storage/app/open-api.yaml`` location.

Models database schema is being cached for performance (1d TTL), 
if you modify a migration be sure to run ``php artisan voice:open-api --bust-cache``
which will force re-caching. 


Stay tuned 
