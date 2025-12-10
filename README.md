# checkSelectForUpdate

# please, run the following commands in a terminal(ignore the sign - $):
$ docker compose up
# in logs you need to pay attention for requests to "select_ms_nginx" in order to make sure there are concurrent requests

# during "composer install" there would be several errors in a directory vendor/owlcorp/doctrine-microseconds-datetime/src/DBAL/Types
# regarding to types compatibilities... Those needs to be fixed on the spot... 
$ docker exec select_ms composer install
$ docker exec select_ms bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
$ docker exec select_ms bin/console doctrine:fixtures:load --no-interaction


# it makes a call in a console:
$ curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' & curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' 

