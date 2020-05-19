db.createUser(
    {
        user: "root",
        pwd: "root",
        roles: [
            {
                role: "readWrite",
                db: "appquarium_apiuser"
            }
        ]
    }
);