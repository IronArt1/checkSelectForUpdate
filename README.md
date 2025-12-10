# checkSelectForUpdate

# please, run the following commands in a terminal(ignore the sign - $):
$ docker compose up -d
$ docker exec select_ms bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
$ docker exec select_ms bin/console doctrine:fixtures:load --no-interaction


# it makes a call in a console:
$ curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' & curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' 

