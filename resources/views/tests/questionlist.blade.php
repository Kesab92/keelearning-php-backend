@foreach($test->testQuestions()->orderBy('position')->get() as $testQuestion)
    <div class="item" data-test-question-id="{{ $testQuestion->id }}">
        <div class="right floated content" style="padding-right: 10px;">
            <div class="ui input" style="width: 100px;">
                <input type="number" class="points" style="width: 100%;" value="{{ $testQuestion->points }}" placeholder="Punkte">
            </div>
            <button class="delete-question ui icon button">
                <i class="delete icon"></i>
            </button>
        </div>
        <div class="content" style="padding-left: 10px;">
            <div class="header">
                {{ $testQuestion->question->title }}
            </div>
            @if($testQuestion->question->category)
                <div class="description">{{ $testQuestion->question->category->name }}</div>
            @endif
        </div>
    </div>
@endforeach
