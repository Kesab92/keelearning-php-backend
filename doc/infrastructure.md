# Infrastruktur mit Kubernetes
## Benötigte Services
* MySQL - https://github.com/Yolean/kubernetes-mysql-cluster
* Redis - https://github.com/kubernetes/examples/blob/master/staging/storage/redis/README.md
* Deepstream - siehe Brotheld
* Backend - dieses Repo
* Frontends
* nginx SSL Termination und Reverseproxy - https://hub.docker.com/r/jrcs/letsencrypt-nginx-proxy-companion/
* glusterfs für Dateien - https://github.com/gluster/gluster-kubernetes


## Server
* 3 Hetzner EX...
* GitLab Instanz
* GitLab - Runner für Builds
* GitLab - Docker Registry als ContainerRegistry


