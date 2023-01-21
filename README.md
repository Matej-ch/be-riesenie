# BE riesenie

* [Zadanie](DOCS/ASSIGNMENT.md)
* [ER diagram](DOCS/ERD.png)
* [SQL](DOCS/SQL.md)

![ER diagram](DOCS/ERD.png)

#### Solution without api platform is in branch

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
    * **GET** /api/products?page=1&name=nvidia
* Query parameter **category** will return products based on their category
    * **GET** /api/products?page=1&category=gpu
* Query parameter **price** with gt, lt, lte, gte
    * **GET** /api/products?page=1&price[gt]=100&price[lt]=200

### Elasticsearch

* For using ElasticsSearch in our api, we need to install Elasticsearch instance on our machine,
  and it's best to use already existing package
  like [friendsofsymfony/elastica-bundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle)
  making implementation simple

* After that configure bundle and port (default 9200)
* Define indices on entity product
* After proper setup every new or modified entity will be updated automatically in Elasticsearch