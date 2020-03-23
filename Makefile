.PHONY: all build release

srv=andreisura-weatherapp
repo=andreisura-weatherapp
shorthash=`git rev-parse --short HEAD`
base=us.gcr.io/sharpspring-us/$(repo)
branch=$${BRANCH_NAME:-`git rev-parse --abbrev-ref HEAD`}
image=$(base):$(shorthash)

# -- dev helpers
pod=`kubectl --context=staging -n $(srv) get pods | tail -1 | cut -d ' ' -f1`
run:
	docker run --rm --name php-test php-test-image

jenk:
	open https://jenkins.privatesharp.com/job/SharpSpring/job/andreisura-weatherapp/job/master/

k_stag_pods:
	kubectl --context=staging -n $(srv) get po
k_stag_desc:
	kubectl --context=staging -n $(srv) describe po $(pod)
k_stag_logs:
	kubectl --context=staging -n $(srv) logs $(pod)
k_stag_watch:
	kubectl --context=staging -n $(srv) logs -f $(pod)
k_stag_srv:
	kubectl --context=staging -n $(srv) get services


buildtest:
	# https://docs.docker.com/engine/reference/commandline/build/
	docker build -t php-test-image .


# === sys
all: build release

build:
	docker build -t $(image) .
	docker tag $(image) $(base):$(branch)

release:
	docker push $(image)
	docker push $(base):$(branch)
