# Items im GitHub Project von #1 bis #19 neu sortieren
$OWNER          = "alakla"
$PROJECT_ID     = "PVT_kwHOBUrpt84BQ6PQ"
$PROJECT_NUMBER = 11

Write-Host "Hole alle Items aus dem Projekt..." -ForegroundColor Cyan

$itemsResult = gh project item-list $PROJECT_NUMBER --owner $OWNER --format json | ConvertFrom-Json

# Items nach Issue-Nummer aufsteigend sortieren
$sortedItems = $itemsResult.items | Where-Object { $_.content.number -ne $null } | Sort-Object { $_.content.number }

Write-Host "$($sortedItems.Count) Items gefunden. Sortiere von #1 bis #$($sortedItems.Count)..." -ForegroundColor Green
Write-Host ""

# Erstes Item an erste Position (afterId = null)
$firstItem = $sortedItems[0]
$mutation = '{ "query": "mutation { updateProjectV2ItemPosition(input: { projectId: \"' + $PROJECT_ID + '\" itemId: \"' + $firstItem.id + '\" }) { items { nodes { id } } } }" }'
$mutation | Out-File -FilePath "$env:TEMP\gql_pos.json" -Encoding utf8
Get-Content "$env:TEMP\gql_pos.json" | gh api graphql --input - | Out-Null
Write-Host "  Issue #$($firstItem.content.number) -> Position 1 (erste)" -ForegroundColor Gray

# Restliche Items nacheinander positionieren
for ($i = 1; $i -lt $sortedItems.Count; $i++) {
    $currentItem  = $sortedItems[$i]
    $previousItem = $sortedItems[$i - 1]

    $mutation = '{ "query": "mutation { updateProjectV2ItemPosition(input: { projectId: \"' + $PROJECT_ID + '\" itemId: \"' + $currentItem.id + '\" afterId: \"' + $previousItem.id + '\" }) { items { nodes { id } } } }" }'
    $mutation | Out-File -FilePath "$env:TEMP\gql_pos.json" -Encoding utf8
    Get-Content "$env:TEMP\gql_pos.json" | gh api graphql --input - | Out-Null
    Write-Host "  Issue #$($currentItem.content.number) -> Position $($i + 1)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Fertig! Items sind jetzt von #1 bis #19 sortiert." -ForegroundColor Green
gh project view $PROJECT_NUMBER --owner $OWNER --web
