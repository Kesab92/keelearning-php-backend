<div class="ui stacked segments">
    <div class="ui segment">
        <h3 class="indexcard-front editable">{!! $indexcard->front !!}</h3>
    </div>

    <div class="ui segment">
      @if($indexcard->type == App\Models\IndexCard::TYPE_STANDARD)
        <div class="editable indexcard-back">{!! $indexcard->back !!}</div>
      @endif
      @if($indexcard->type == App\Models\IndexCard::TYPE_IMAGEMAP)
        <div class="item">
          <button class="imagemap-button ui grey button">
            Text-in-Bild-Zuweisung bearbeiten
          </button>
          <input class="indexcard-json" type="hidden" name="json" value="{{ $indexcard->json }}">
        </div>
      @endif
    </div>

    <div class="ui segment">
        <h4>Kategorie</h4>
        <div class="ui right labeled input">
            <select required name="category" class="ui dropdown">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @if ($category->id === $indexcard->category_id) selected @endif>
                        @if ($settings->getValue('use_subcategory_system') && $category->categorygroup)
                            {{ $category->categorygroup->name }}:
                        @endif
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="ui segment">
        @if($indexcard->cover_image_url)
            <img class="indexcard-image-preview" src="{{ $indexcard->cover_image_url }}" width="200">
            <p><a href="/indexcards/{{ $indexcard->id }}/deleteimage" class="ui button red">Bild löschen</a></p>
            <p>Bild austauschen:</p>
        @else
            <p>Bild hochladen:</p>
        @endif
        <div id="indexcard-dropzone">
            <form method="post" action="/indexcards/{{ $indexcard->id }}/image" id="my-dropzone" class="dropzone"></form>
        </div>
    </div>
    <div class="ui bottom attached menu">
        <div class="item">
            <button data-indexcard-id="{{ $indexcard->id }}" class="save-button ui primary button">
                Speichern
            </button>
        </div>
        <div class="menu right">
            <div class="item">
                <a href="/indexcards/{{ $indexcard->id }}/delete" class="delete-indexcard ui red has-popup button" data-content="Löscht die Karte. Sollte nur in Ausnahmefällen gemacht werden, da die Karte auch bei Nutzern verschwindet die bereits mit ihr gelernt haben.">Löschen</a>
            </div>
        </div>
    </div>
</div>

@if($indexcard->type == App\Models\IndexCard::TYPE_IMAGEMAP && $indexcard->cover_image_url)
    <div class="imagemap-modal ui basic modal">
        <div class="header menu">
            <div class="imagemap-button-save ui green inverted button">
              <i class="checkmark icon"></i>
              Fertig
            </div>
            {{--
            <div class="imagemap-button-abort ui red inverted button">
              <i class="remove icon"></i>
              Abbrechen
            </div>
            --}}
            <div class="imagemap-button-addlabel ui blue inverted button float-right">
              <i class="plus icon"></i>
              Neues Label
            </div>
        </div>
        <div class="imagemap-wrapper">
          <img class="image" src="{{ $indexcard->cover_image_url }}">
          <div class="imagemap-labels">

          </div>
        </div>
    </div>
@endif
