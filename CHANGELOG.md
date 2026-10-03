# Changelog

## 1.1.0
- Compatibilité thèmes Classic, Hummingbird (PrestaShop 9) et Warehouse (et thèmes enfants) : les gabarits panier et commande du module étendent désormais ceux du thème actif au lieu d'en recopier une version générée à l'installation (cette copie provoquait une erreur 500 sous Warehouse).
- Plus d'appel à `Tools::displayPrice` (supprimé en PrestaShop 9) ; JavaScript natif sans jQuery ; hook `header` remplacé par `displayHeader`.
- PrestaShop 1.6 : bouton d'arrondi sous le panier via `displayShoppingCartFooter`.
- Les produits « Arrondi » sont créés à la demande (plus de création de 99 produits à l'installation).
- Correction : l'annulation de l'arrondi (« Ne plus arrondir mon panier ») ; un total déjà entier n'est plus arrondi.
- Suppression de `vendor/`, `composer.*` et d'une copie de template de thème client.
- Encart « Besoin d'aller plus loin ? » (accompagnement Websource) dans la page de configuration : visible uniquement des super-administrateurs du back-office, jamais en boutique ni dans les e-mails, masquable 30 jours. Au clic sur « Nous contacter » / « Prendre rendez-vous », le nom du module, sa version, la version de PrestaShop et l'adresse du site sont transmis dans l'URL (paramètres utm_* et ws_*).
