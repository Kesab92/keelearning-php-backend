<div style="width: 100%; overflow-x: scroll;">
    <table class="ui selectable striped table sortable nofrontendsort table-player-stats">
        <thead>
            <tr>
                <?php $sortDirection = $sortDesc ? 'descending' : 'ascending'; ?>
                @if($settings->getValue('save_user_ip_info') && $showPersonalData)
                    <th data-sort="country" @if($sortBy == 'country') class="{{ $sortDirection }}" @endif></th>
                @endif
                @if($showPersonalData)
                <th data-sort="username" @if($sortBy == 'username') class="{{ $sortDirection }}" @endif>
                    Benutzer
                </th>
                @endif
                <th @if($sortBy == 'all') class="{{ $sortDirection }}" @endif data-sort="all">
                    Alle Fragen
                </th>
                @if($players->total())
                    @foreach (reset($players->toArray()['data'])['stats']['categories'] as $id => $category)
                        <th @if($sortBy == 'category-'.$id) class="{{ $sortDirection }}" @endif data-sort="category-{{ $id }}">
                            {{ $category['name'] }}
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($players as $player)
                <tr class="player-stats" data-player-tags="{{ $player->stats['tags'] }}">
                    @if($settings->getValue('save_user_ip_info') && $showPersonalData)
                      <td>
                        {{ App\Services\Emoji::getCountryFlag($player->country) }}&nbsp;
                      </td>
                    @endif
                    @if($showPersonalData)
                        <td>
                            {{ $player->getDisplayNameBackend($showEmails) }}
                        </td>
                    @endif
                    @if ($player->stats['all']['average_box'])
                        <td style="{{ boxPercentagesToCssGradient($player->stats['all'], true) }}" class="cell-gradient-bg">
                            {{ round($player->stats['all']['average_box'], 2) }}
                        </td>
                    @else
                        <td>
                            n/a
                        </td>
                    @endif
                    @foreach($player->stats['categories'] as $category)
                        @if ($category['average_box'])
                            <td style="{{ boxPercentagesToCssGradient($category, true) }}" class="cell-gradient-bg">
                                {{ round($category['average_box'], 2) }}
                            </td>
                        @else
                            <td>
                                n/a
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $players->links() }}
</div>
