library 'jenkins-utils'

node("k8s") {
  stage("Checkout") {
    checkout scm
  }

  stage("Build") {
    sh("make build")
  }

  stage("Release") {
    sh("make release")
  }

  stage("Deploy") {
    getKubeconfig()
    k8s_contexts = [
      "andreisura-weatherapp"
    ]
    k8s_contexts.each { cluster ->
      template(cluster: cluster)
      sh("kubectl --context ${cluster} apply -f ./tmp-k8s")
    }
  }
}
