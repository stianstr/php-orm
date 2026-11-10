.PHONY: test
test:
	mysql < test/helpers/fixture.sql
	phpunit --stop-on-error --stop-on-failure -d error_reporting=-1 --bootstrap test/helpers/bootstrap.php test/
