# GitHub Project Board erstellen und Issues in Backlog einfuegen
$OWNER = "alakla"
$REPO  = "als-panel"

Write-Host "============================================" -ForegroundColor Cyan
Write-Host " ALS Panel - GitHub Project erstellen" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# 1. Projekt erstellen oder bestehendes verwenden
Write-Host "[1/4] Suche bestehendes Projekt oder erstelle neues..." -ForegroundColor Yellow

$projectList = gh project list --owner $OWNER --format json | ConvertFrom-Json
$existingProject = $projectList.projects | Where-Object { $_.title -like "*ALS Panel*" } | Select-Object -First 1

if ($existingProject) {
    $PROJECT_NUMBER = $existingProject.number
    $PROJECT_ID     = $existingProject.id
    Write-Host "      Bestehendes Projekt gefunden: Nr. $PROJECT_NUMBER" -ForegroundColor Green
} else {
    $createOutput = gh project create --owner $OWNER --title "ALS Panel - Webbasiertes Verwaltungssystem" 2>&1
    Write-Host "      Erstellt: $createOutput" -ForegroundColor Gray
    Start-Sleep -Seconds 2
    $projectList = gh project list --owner $OWNER --format json | ConvertFrom-Json
    $existingProject = $projectList.projects | Where-Object { $_.title -like "*ALS Panel*" } | Select-Object -First 1
    $PROJECT_NUMBER = $existingProject.number
    $PROJECT_ID     = $existingProject.id
    Write-Host "      Neues Projekt erstellt: Nr. $PROJECT_NUMBER" -ForegroundColor Green
}

Write-Host "      Projekt-ID  : $PROJECT_ID" -ForegroundColor Gray
Write-Host "      Projekt-Nr. : $PROJECT_NUMBER" -ForegroundColor Gray
Write-Host ""

# 2. Alle Issues laden
Write-Host "[2/4] Lade alle Issues..." -ForegroundColor Yellow
$issues = gh issue list --repo "$OWNER/$REPO" --state all --limit 50 --json number,url | ConvertFrom-Json
Write-Host "      $($issues.Count) Issues gefunden." -ForegroundColor Green
Write-Host ""

# 3. Issues zum Projekt hinzufuegen
Write-Host "[3/4] Fuege Issues zum Projekt hinzu..." -ForegroundColor Yellow
foreach ($issue in $issues) {
    $output = gh project item-add $PROJECT_NUMBER --owner $OWNER --url $issue.url 2>&1
    Write-Host "      Issue #$($issue.number) hinzugefuegt" -ForegroundColor Gray
}
Write-Host "      Alle Issues hinzugefuegt." -ForegroundColor Green
Write-Host ""

# 4. Status auf Backlog/Todo setzen via GraphQL
Write-Host "[4/4] Setze Status fuer alle Items..." -ForegroundColor Yellow

# Status-Feld via GraphQL holen
$fieldResult = gh api graphql -f query="
query {
  node(id: \"$PROJECT_ID\") {
    ... on ProjectV2 {
      fields(first: 20) {
        nodes {
          ... on ProjectV2SingleSelectField {
            id
            name
            options { id name }
          }
        }
      }
    }
  }
}" | ConvertFrom-Json

$statusField = $fieldResult.data.node.fields.nodes | Where-Object { $_.name -eq "Status" }

if (-not $statusField) {
    Write-Host "      Status-Feld nicht gefunden. Bitte manuell setzen." -ForegroundColor Red
} else {
    $STATUS_FIELD_ID = $statusField.id

    $backlogOption = $statusField.options | Where-Object { $_.name -eq "Backlog" }
    if (-not $backlogOption) {
        $backlogOption = $statusField.options | Where-Object { $_.name -eq "Todo" }
        Write-Host "      Kein 'Backlog' gefunden -> verwende 'Todo'" -ForegroundColor Yellow
    }
    $OPTION_ID = $backlogOption.id
    Write-Host "      Status-Option: '$($backlogOption.name)'" -ForegroundColor Gray

    # Alle Items holen
    $itemsResult = gh project item-list $PROJECT_NUMBER --owner $OWNER --format json | ConvertFrom-Json

    foreach ($item in $itemsResult.items) {
        gh api graphql -f query="
mutation {
  updateProjectV2ItemFieldValue(input: {
    projectId: \"$PROJECT_ID\"
    itemId: \"$($item.id)\"
    fieldId: \"$STATUS_FIELD_ID\"
    value: { singleSelectOptionId: \"$OPTION_ID\" }
  }) {
    projectV2Item { id }
  }
}" | Out-Null
        Write-Host "      Item $($item.id) -> Status gesetzt" -ForegroundColor Gray
    }
    Write-Host "      Alle Status gesetzt." -ForegroundColor Green
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host " Fertig! Oeffne Projekt im Browser..." -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
gh project view $PROJECT_NUMBER --owner $OWNER --web
