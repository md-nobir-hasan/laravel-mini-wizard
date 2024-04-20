# laravel-curd-by-command
Everything in one command 

### Some notes
    1. Don't use name field. Always try to use title field instead of name which will use to session message. and try to use title field for every table.
    1. Just one name only model name will use for the all name. Example shows for "(Photo), (OrderItem)".
    2. Table(migration) name will be snake case and plural of model. Example:- (photos),(order_items).
    3. Controller name will be  (PhotoController), (OrderItemController).
    4. Permissions name will be  (Show Photo, Create Photo, Edit Photo, Delete Photo), (Show OrderItem, Create  OrderItem, Edit OrderItem, Delete OrderItem).
    5. Requests name will be  (StorePhotoRequest, UpdatePhotoRequest), (StoreOrderItemRequest, UpdateOrderItemRequest).
    6. Every migration default contain title, status, serial field. You can modified these from the migration stub file.
    7. For automatice implementation of seeder or factory you have to use )}//n in the DatabaseSeeder.php 

### Improveable note
    1. every datatype validation will added
    2. every inpute filed in create and edit file will added

### Warning
    1. You got a error that "This model '---' already exist." Rather than deleting your model,  you have to choose another name. If you want to delete the model you have to delete the migration, controller, requests, views, seeder,factories also with this same name.

