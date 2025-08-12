# service-deployment-dashboard

Allow users to monitor APIs by indicating how long they've been in a `stale` state

See bellow image for example:
<img width="1662" height="1014" alt="image" src="https://github.com/user-attachments/assets/6ac40f8e-1132-4094-9131-4dfc57df9ee2" />

---
## Configuration
The App is fairly straight forward, everything is pull from the `.env` variable `SERVICE_API`.
`SERVICE_API` needs to be a JSON formatted string as this example bellow.

```JSON
{
    "YOU_SERVICE_NAME": {
        "Staging": "YOUR_APP_STAGING_URL/healthcheck",
        "Pre-Production": "YOUR_APP_PRE_PRODUCTION_URL/healthcheck",
        "Production": "YOUR_APP_PRODUCTION_URL/healthcheck"
    }
}
```

The Application will dynamically generate the dashboard as long as you follow the JSON formating above, so if you want Mornitore 10 app the application will be able to do it.

---
## Running The Application
Simply pull the app and once you are in the `root` folder you can run:

```bash
symfony server:start
```

Once service is running you will be able to got to the index page to see the Dashboard:
`/index` or if you want a see `health-data` simply go to `/health-data`

---
