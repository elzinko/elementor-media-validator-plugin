# Purpose

This is a wordpress plugin for media validation

## Installation

Check [install](./doc/INSTALL.md) document.

## Bnundle

```bash
npm run bundle
```

## Next features

### Images

- [x] add istockphoto image handling
- [x] add gettyimages image handling
- [x] check image metadata : alt + title + legend
- [ ] add icon handling
- [ ] add theme image handling
- [ ] add Envato image handling
- [ ] add shutterstock image handling
- [ ] add freepik image handling
- [ ] add unsplash image handling

### Others

- [x] bundle plugin
- [x] add export to csv
- [x] add persistant validation control using checkbox
- [x] add shuttershock API client configuration in settings
- [x] filtrer les media qui sont validés
- [ ] add client access
- [ ] add agency image handling
- [ ] add time control
- [ ] add dev documentation
- [ ] check LICENCE for wordpress compatibility and pro version
- [ ] [distribute](https://www.dreamhost.com/blog/how-to-create-your-first-wordpress-plugin/) plugin


## infos 
package.json : Utilisez-le pour les dépendances JavaScript et les scripts liés au développement de votre plugin.composer.json : Utilisez-le pour les dépendances PHP spécifiques à WordPress et pour définir la version minimale requise de PHP, entre autres.Actions GitHub : Utilisez-les pour automatiser les tâches de packaging et de création de releases sur GitHub. Ces actions peuvent être personnalisées en fonction de vos besoins spécifiques.