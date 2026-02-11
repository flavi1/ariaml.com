<div ng-app="CmsDashboard" ng-controller="PostsCtrl">
    <h2>Gestion des Posts</h2>
    
    <input type="text" ng-model="search" placeholder="Filtrer les titres...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="article in posts | filter:search">
                <td>{{ article.id }}</td>
                <td>{{ article.title }}</td>
                <td>
                    <button ng-click="edit(article.id)">Modifier</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>

<script>
    (function() {
        var app = angular.module('CmsDashboard', []);

        app.controller('PostsCtrl', function($scope, $http) {
            // On récupère la langue depuis le segment de l'URL (ex: /fr/posts/dashboard)
            var pathArray = window.location.pathname.split('/');
            var lang = pathArray[1] || 'fr'; 

            $scope.posts = [];

            // Appel direct vers votre point de données
            $http.get('/' + lang + '/posts.json')
                .then(function(response) {
                    $scope.posts = response.data.posts;
                })
                .catch(function(err) {
                    console.error("Erreur lors du chargement des données", err);
                });

            $scope.edit = function(id) {
                console.log("Edition de l'article : " + id);
                // Logique de redirection ou d'ouverture de modal ici
            };
        });
    })();
</script>
