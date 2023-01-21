# BE riesenie

* [Zadanie](DOCS/ASSIGNMENT.md)
* [ER diagram](DOCS/ERD.png)
* [SQL](DOCS/SQL.md)

![ER diagram](DOCS/ERD.png)

#### Solution with symfony an api platform is on main branch

#### Solution without api platform is in branch [without-api-platform](https://github.com/Matej-ch/be-riesenie/tree/without-api-platform)

## API PLATFORM

## Endpointy

* **[GET]** ```/api/products``` listing produktov
    * pagination with **page** query parameter
* **[POST]** ```/api/products``` tvorba produktu
* **[GET]** ```/api/products/{id}``` detail produktu
* **[PATCH]** ```/api/products/{id}``` editacia produktu

### SEARCHING

* Query parameter **name** will look up products based on name
    * _GET_ /api/products?page=1&name=nvidia
* Query parameter **category.name** will return products based on their category
* Query parameter **price** will filter products based on price
    * **price[lte]**, **price[lt]**, **price[gte]**, **price[lt]**

### Elasticsearch

* When using elastic search wit api platform the best way is to use
  package [elasticsearch/elasticsearch](https://github.com/elastic/elasticsearch-php)
* Inside our yaml configuration set host with port number on which elasticsearch runs (default is 9200) and mapping to
  our entity product

---

## WITHOUT API PLATFORM

* **[GET]** ```/api/products``` listing produktov
    * pagination with **page** query parameter
* **[POST]** ```/api/products``` tvorba produktu
* **[GET]** ```/api/products/{id}``` detail produktu
* **[PATCH]** ```/api/products/{id}``` editacia produktu
* **[DELETE]** ```/api/products/{id}``` delete product
    * not implemented, only shown

### CACHING

**1.**

* During **GET** ```/api/products/{id}``` request
    * When someone accesses product detail data is cached, and retrieved if it's not older than 1800 seconds
* Making sure we don't have incorrect data, cache for product should be invalidated during **PATCH
  **  ```/api/products/{id}``` request
* After data is deleted from database with **DELETE** request, remove data inside redis cache and Elasticsearch
* Product data can also be cached in **POST** request when new product is created, Also add to Elasticsearch

**2.**

- Another option to cache products is to create console command and then run it in cron job
- This way we can prepare products and don't have to wait for user to access it, and having worse experience
- If we have too many products caching all of them might be inefficient and costly.
    - But caching products that are accessed the most might be good idea. (Of we will need to track how many times are
      product accessed)

### SEARCHING

* Query parameter **name** will look up products based on name
    * _GET_ /api/products?page=1&name=nvidia
* Query parameter **category** will return products based on their category

### Elasticsearch

* For using ElasticsSearch in our api, we need to install Elasticsearch instance on our machine,
  and it's best to use already existing package
  like [friendsofsymfony/elastica-bundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle)
  making implementation simple

* After that configure bundle and port (default 9200)
* Define indices on entity product
* After proper setup every new or modified entity will be updated automatically in Elasticsearch