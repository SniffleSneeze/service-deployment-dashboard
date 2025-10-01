async function fetchData() {
    try {
        const response = await fetch('/health-data');

        console.info('Page has been refreshed at: ' + new Date().toLocaleString());

        return await response.json();
    } catch (error) {
        console.error("Error fetching data: " + new Date().toLocaleString(), error);
    }
}

function createDashboard(healthData) {
    try {
        const container = document.getElementById("card-container")
        container.innerHTML = "";
        let servicesName = [];

        Object.entries(healthData).forEach(([serviceName, envi]) => {
            servicesName.push(serviceName);

            const serviceCard = document.createElement("div");
            serviceCard.classList.add(`${serviceName}-service`, "service-card");
            serviceCard.id = `${serviceName}-service`;
            container.appendChild(serviceCard);

            const serviceTitle = document.createElement("div");
            serviceTitle.innerHTML = `<h2 class="title">${serviceName}</h2>`;
            serviceTitle.classList.add("title-card");
            serviceCard.appendChild(serviceTitle);

            Object.entries(envi).forEach(([enviName, enviData]) => {
                if (enviName !== "info") {
                    serviceCard.appendChild(createServiceCard(enviData, serviceName, enviName));

                    if (enviName !== "Production") {
                        createArrow(serviceCard);
                        createStaleCard(serviceCard, serviceName, enviName, envi.info);
                        createArrow(serviceCard);
                    }
                }
            });
        });

        // will update background-colour of card if last commit is more than 1days
        flagDiff(servicesName);
    } catch (error) {
        console.error("Error creating dashboard: " + new Date().toLocaleString(), error);
    }
}

function createServiceCard(enviData, serviceName, enviName) {

    try {
        const card = document.createElement("div");
        card.classList.add("card", "success");
        card.id = serviceName + "-" + enviName + "-card";

        const subTitle = document.createElement("h3");
        subTitle.textContent = enviName;
        card.appendChild(subTitle);

        const appStatus = document.createElement("h4");
        appStatus.textContent = enviData.app ? "Application Online ✅" : "Application Offline ❌";
        card.appendChild(appStatus);

        const version = document.createElement("p");
        version.innerHTML = `<strong>Version:</strong> ${enviData.version}`;
        card.appendChild(version);

        const dateFormatter = new Intl.DateTimeFormat('en-GB', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });

        const lastCommitDate = dateFormatter.format(new Date(enviData.lastCommitDate));
        const lastBuildStartTime = dateFormatter.format(new Date(enviData.lastBuildStartTime));

        const commitAndBuild = document.createElement("p");
        commitAndBuild.innerHTML = `
        <strong>Last Commit:</strong> ${lastCommitDate} <br>
        <strong>Last Build Start:</strong> ${lastBuildStartTime}
    `;
        card.appendChild(commitAndBuild);
        return card;
    } catch (error) {
        console.error("Error creating card: " + new Date().toLocaleString(), error);
    }
}

function createArrow(serviceContainer) {
    try {
        const arrowDown = document.createElement("div");
        arrowDown.classList.add("arrow");
        arrowDown.textContent = "↓";
        serviceContainer.appendChild(arrowDown);
    } catch (error) {
        console.error("Error trying to create `Arrow`: " + new Date().toLocaleString(), error);
    }
}

function createStaleCard(serviceContainer, serviceName, enviName, infoValue) {
    try {
        const infoCard = document.createElement("div");
        infoCard.classList.add("stale-card", "success");
        infoCard.textContent =
            infoValue.preProductionDiff === 0 ?
                "Up to Date" :
                `${infoValue.preProductionDiff} days stale with Staging`;
        infoCard.id = serviceName + "-stagingDiff"
        infoCard.dataset.value = infoValue.preProductionDiff;

        if (enviName === "Pre-Production") {
            infoCard.textContent =
                infoValue.productionDiff === 0 ?
                    "Up to Date" : `${infoValue.productionDiff} days stale with ${enviName}`;
            infoCard.id = serviceName + "-preProductionDiff"
            infoCard.dataset.value = infoValue.productionDiff;
        }

        serviceContainer.appendChild(infoCard);
    } catch (error) {
        console.error("Error creating Stale card: " + new Date().toLocaleString(), error);
    }
}

function flagDiff(servicesName) {
    try {
        servicesName.forEach(serviceName => {
            updateStatus(`#${serviceName}-stagingDiff`,  `#${serviceName}-Pre-Production-card`);
            updateStatus( `#${serviceName}-preProductionDiff`,   `#${serviceName}-Production-card`);
        })
    } catch (error) {
        console.error("Error during FlagDiff: " + new Date().toLocaleString(), error);
    }
}

function updateStatus(diffSelector, cardSelector) {
    const diffCard = document.querySelector(diffSelector);
    const targetCard = document.querySelector(cardSelector);
    const diffValue = Number(diffCard.dataset.value);

    if (diffValue > 0) {
        diffCard.classList.remove("success");
        targetCard.classList.remove("success");

        if (diffValue === 1) {
            diffCard.classList.add("warning");
            targetCard.classList.add("warning");
        } else {
            diffCard.classList.add("error");
            targetCard.classList.add("error");
        }
    }
}

// fetch data every 5 minutes
setInterval(fetchData, 60 * 10 * 1000); //

// fetch data and create dashboard
fetchData().then((response) => createDashboard(response));

