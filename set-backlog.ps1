# Status aller Items auf Backlog/Todo setzen
$OWNER         = "alakla"
$PROJECT_ID    = "PVT_kwHOBUrpt84BQ6PQ"
$PROJECT_NUMBER = 11

Write-Host "Hole Status-Feld..." -ForegroundColor Cyan

# GraphQL Query in Temp-Datei schreiben
$fieldQuery = '{ "query": "query { node(id: \"' + $PROJECT_ID + '\") { ... on ProjectV2 { fields(first: 20) { nodes { ... on ProjectV2SingleSelectField { id name options { id name } } } } } } }" }'
$fieldQuery | Out-File -FilePath "$env:TEMP\gql_field.json" -Encoding utf8

$fieldResult = Get-Content "$env:TEMP\gql_field.json" | gh api graphql --input - | ConvertFrom-Json
$statusField = $fieldResult.data.node.fields.nodes | Where-Object { $_.name -eq "Status" }

if (-not $statusField) {
    Write-Host "Status-Feld nicht gefunden!" -ForegroundColor Red
    exit
}

$STATUS_FIELD_ID = $statusField.id
Write-Host "Status-Feld ID: $STATUS_FIELD_ID" -ForegroundColor Gray

$backlogOption = $statusField.options | Where-Object { $_.name -eq "Backlog" }
if (-not $backlogOption) {
    $backlogOption = $statusField.options | Where-Object { $_.name -eq "Todo" }
    Write-Host "Verwende 'Todo' als Backlog" -ForegroundColor Yellow
}
$OPTION_ID = $backlogOption.id
Write-Host "Option: '$($backlogOption.name)' (ID: $OPTION_ID)" -ForegroundColor Gray
Write-Host ""

# Alle Items holen
Write-Host "Hole alle Items..." -ForegroundColor Cyan
$itemsResult = gh project item-list $PROJECT_NUMBER --owner $OWNER --format json | ConvertFrom-Json
Write-Host "$($itemsResult.items.Count) Items gefunden." -ForegroundColor Green
Write-Host ""

# Status fuer jedes Item setzen
Write-Host "Setze Status..." -ForegroundColor Cyan
foreach ($item in $itemsResult.items) {
    $mutation = '{ "query": "mutation { updateProjectV2ItemFieldValue(input: { projectId: \"' + $PROJECT_ID + '\" itemId: \"' + $item.id + '\" fieldId: \"' + $STATUS_FIELD_ID + '\" value: { singleSelectOptionId: \"' + $OPTION_ID + '\" } }) { projectV2Item { id } } }" }'
    $mutation | Out-File -FilePath "$env:TEMP\gql_mutation.json" -Encoding utf8
    Get-Content "$env:TEMP\gql_mutation.json" | gh api graphql --input - | Out-Null
    Write-Host "  Item $($item.id) -> '$($backlogOption.name)'" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Fertig! Alle Items auf '$($backlogOption.name)' gesetzt." -ForegroundColor Green
gh project view $PROJECT_NUMBER --owner $OWNER --web
