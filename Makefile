.PHONY: all build release

repo=andreisura-weatherapp
shorthash=`git rev-parse --short HEAD`
base=us.gcr.io/sharpspring-us/$(repo)
branch=$${BRANCH_NAME:-`git rev-parse --abbrev-ref HEAD`}
image=$(base):$(shorthash)

# -- dev helpers
run:
	docker run --rm --name php-test php-test-image

buildtest:
	# https://docs.docker.com/engine/reference/commandline/build/
	docker build -t php-test-image .


all: build release

build:
	docker build -t $(image) .
	docker tag $(image) $(base):$(branch)

release:
	docker push $(image)
	docker push $(base):$(branch)
