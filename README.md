# checkSelectForUpdate

# please, run the following commands in a terminal(ignore the sign - $):
$ docker compose up
# in logs you need to pay attention for requests to "select_ms_nginx" in order to make sure there are concurrent requests

# during "composer install" there would be several errors in a directory vendor/owlcorp/doctrine-microseconds-datetime/src/DBAL/Types
# regarding to types compatibilities... Those needs to be fixed on the spot... 
$ docker exec select_ms composer install
$ sudo chmod 775 -R vendor
# change "arthure" to your user name
$ sudo chown arthure.apache -R vendor
# just add types(string OR mixed) to the following files:
OwlCorp\DoctrineMicrotime\DBAL\Types\BaseDateTimeMicroWithoutTz::getSQLDeclaration(...): string
OwlCorp\DoctrineMicrotime\DBAL\Types\DateTimeMicroType::convertToDatabaseValue(...): mixed
OwlCorp\DoctrineMicrotime\DBAL\Types\DateTimeMicroType::convertToPHPValue(...): mixed
OwlCorp\DoctrineMicrotime\DBAL\Types\DateTimeImmutableMicroType::convertToDatabaseValue(): mixed
OwlCorp\DoctrineMicrotime\DBAL\Types\DateTimeImmutableMicroType::convertToPHPValue(...): mixed



$ docker exec select_ms bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
$ docker exec select_ms bin/console doctrine:fixtures:load --no-interaction


# (TEST 1 is) it makes a call in a console:
$ curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' & curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase -d '{"userId":1,"itemId":1}' 

# (TEST 2 is) it makes a call in a console (10 threads by 10 times are running):
$ seq 1 1000 | xargs -I $ -n1 -P10 curl -X POST -H "Content-Type: application/json" http://localhost:8077/make/purchase2 -d '{"userId":1,"itemId":1}'
