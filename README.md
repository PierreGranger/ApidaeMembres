## Docker

```bash
cd docker
cp .env.sample .env   # optionnel (PORT=80 par défaut)
docker compose up -d --build
```

Exemples : http://localhost/examples/  
Recette : http://localhost/recette/  
Tests : `docker compose exec php composer test`

Copiez `config.sample.inc.php` en `config.inc.php` avant d'utiliser les exemples.

_____

### Class: \PierreGranger\ApidaeMembres

> Documentation pour la classe ApidaeMembres
