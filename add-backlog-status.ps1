# Backlog Status zum GitHub Project hinzufuegen und alle Items auf Backlog setzen
$OWNER          = "alakla"
$PROJECT_ID     = "PVT_kwHOBUrpt84BQ6PQ"
$PROJECT_NUMBER = 11

Write-Host "============================================" -ForegroundColor Cyan
Write-Host " Backlog Status hinzufuegen" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# 1. Status-Feld ID holen
Write-Host "[1/3] Hole Status-Feld..." -ForegroundColor Yellow
$fieldQuery = '{ "query": "query { node(id: \"' + $PROJECT_ID + '\") { ... on ProjectV2 { fields(first: 20) { nodes { ... on ProjectV2SingleSelectField { id name options { id name } } } } } } }" }'
$fieldQuery | Out-File -FilePath "$env:TEMP\gql_field.json" -Encoding utf8
$fieldResult = Get-Content "$env:TEMP\gql_field.json" | gh api graphql --input - | ConvertFrom-Json
$statusField = $fieldResult.data.node.fields.nodes | Where-Object { $_.name -eq "Status" }
$STATUS_FIELD_ID = $statusField.id
Write-Host "      Status-Feld ID: $STATUS_FIELD_ID" -ForegroundColor Gray
Write-Host "      Aktuelle Optionen: $($statusField.options.name -join ', ')" -ForegroundColor Gray
Write-Host ""

# 2. Backlog Option hinzufuegen
Write-Host "[2/3] Fuege 'Backlog' als erste Option hinzu..." -ForegroundColor Yellow
$addOptionMutation = '{ "query": "mutation { addProjectV2SingleSelectFieldOptionToField(input: { projectId: \"' + $PROJECT_ID + '\" fieldId: \"' + $STATUS_FIELD_ID + '\" name: \"Backlog\" color: GRAY position: FIRST }) { field { ... on ProjectV2SingleSelectField { id name options { id name } } } } }" }'
$addOptionMutation | Out-File -FilePath "$env:TEMP\gql_add_option.json" -Encoding utf8
$addResult = Get-Content "$env:TEMP\gql_add_option.json" | gh api graphql --input - | ConvertFrom-Json

if ($addResult.errors) {
    Write-Host "      Fehler: $($addResult.errors[0].message)" -ForegroundColor Red
    exit
}

$newOptions = $addResult.data.addProjectV2SingleSelectFieldOptionToField.field.options
$backlogOption = $newOptions | Where-Object { $_.name -eq "Backlog" }
$BACKLOG_OPTION_ID = $backlogOption.id
Write-Host "      'Backlog' hinzugefuegt! ID: $BACKLOG_OPTION_ID" -ForegroundColor Green
Write-Host "      Neue Optionen: $($newOptions.name -join ' -> ')" -ForegroundColor Gray
Write-Host ""

# 3. Alle Items auf Backlog setzen
Write-Host "[3/3] Setze alle Items auf 'Backlog'..." -ForegroundColor Yellow
$itemsResult = gh project item-list $PROJECT_NUMBER --owner $OWNER --format json | ConvertFrom-Json

foreach ($item in $itemsResult.items) {
    $mutation = '{ "query": "mutation { updateProjectV2ItemFieldValue(input: { projectId: \"' + $PROJECT_ID + '\" itemId: \"' + $item.id + '\" fieldId: \"' + $STATUS_FIELD_ID + '\" value: { singleSelectOptionId: \"' + $BACKLOG_OPTION_ID + '\" } }) { projectV2Item { id } } }" }'
    $mutation | Out-File -FilePath "$env:TEMP\gql_mutation.json" -Encoding utf8
    Get-Content "$env:TEMP\gql_mutation.json" | gh api graphql --input - | Out-Null
    Write-Host "      Issue #$($item.content.number) -> Backlog" -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host " Fertig! Alle Items sind jetzt im Backlog." -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
gh project view $PROJECT_NUMBER --owner $OWNER --web
