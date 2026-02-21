(function($) {
    $(function() {
        if($('#mbti-test-app').length === 0) return;

        var questions = mbti_questions || [];
        var total = questions.length;
        var currentIdx = 0;
        var answers = {};
        
        // Load from LocalStorage
        var savedAnswers = localStorage.getItem('mbti_saved_answers');
        if(savedAnswers) {
            try {
                answers = JSON.parse(savedAnswers);
                var lastAnsweredIdx = Object.keys(answers).length;
                if(lastAnsweredIdx > 0 && lastAnsweredIdx < total) {
                    if(confirm("이전에 답변하던 내역이 있습니다. 이어서 하시겠습니까?")) {
                        currentIdx = lastAnsweredIdx;
                    } else {
                        answers = {};
                        localStorage.removeItem('mbti_saved_answers');
                    }
                } else if(lastAnsweredIdx >= total) {
                    answers = {};
                    localStorage.removeItem('mbti_saved_answers');
                }
            } catch(e) {
                console.error(e);
            }
        }

        var $slider = $('#mbti-question-slider');
        var $progressFill = $('#mbti-progress-fill');
        var $currentQNum = $('#current-q-num');
        var $btnPrev = $('#btn-prev-q');
        var $app = $('#mbti-test-app');
        var $loading = $('#mbti-loading');

        // Render Question UI
        function renderQuestions() {
            var html = '';
            for(var i=0; i<total; i++) {
                var q = questions[i];
                var displayStyle = (i === currentIdx) ? 'block' : 'none';
                html += '<div class="mbti-q-slide" id="q-slide-' + i + '" style="display:' + displayStyle + '">';
                html += '  <h2 class="q-title"><span class="q-num">Q' + (i+1) + '.</span> ' + q.q + '</h2>';
                html += '  <div class="q-options">';
                html += '    <button type="button" class="mbti-option-btn op-a" data-idx="' + i + '" data-val="'+q.a.value+'">' + q.a.text + '</button>';
                html += '    <button type="button" class="mbti-option-btn op-b" data-idx="' + i + '" data-val="'+q.b.value+'">' + q.b.text + '</button>';
                html += '  </div>';
                html += '</div>';
            }
            $slider.html(html);
            updateProgress();
            $app.fadeIn(300);
        }

        function updateProgress() {
            var pct = ((currentIdx) / total) * 100;
            $progressFill.css('width', pct + '%');
            $currentQNum.text(currentIdx + 1 > total ? total : currentIdx + 1);
            if(currentIdx > 0) {
                $btnPrev.show();
            } else {
                $btnPrev.hide();
            }
            
            // Highlight selected option if moving backward
            if(answers[questions[currentIdx]?.id]) {
               var val = answers[questions[currentIdx].id];
               $('#q-slide-'+currentIdx+' .mbti-option-btn').removeClass('selected');
               $('#q-slide-'+currentIdx+' .mbti-option-btn[data-val="'+val+'"]').addClass('selected');
            }
        }

        // Submitting
        function submitTest() {
            $app.hide();
            $loading.fadeIn(200);
            
            var params = {
                answers: answers
            };

            exec_json('mbti.procMbtiSubmit', params, function(ret_obj) {
                if(ret_obj.error == 0) {
                    localStorage.removeItem('mbti_saved_answers');
                    // Redirect to result page
                    var url = current_url.setQuery('act', 'dispMbtiResult').setQuery('result_srl', ret_obj.result_srl);
                    window.location.href = url;
                } else {
                    alert(ret_obj.message);
                    $loading.hide();
                    $app.fadeIn(200);
                }
            });
        }

        // Event Listeners
        var isAnimating = false; // Add flag to prevent double clicks

        $('body').on('click', '.mbti-option-btn', function() {
            if (isAnimating) return; // Ignore clicks if already transitioning
            isAnimating = true;

            var $this = $(this);
            var idx = parseInt($this.data('idx'));
            var val = $this.data('val');
            var qId = questions[idx].id;
            
            $this.siblings().removeClass('selected');
            $this.addClass('selected');
            
            answers[qId] = val;
            
            // Save to localStorage
            localStorage.setItem('mbti_saved_answers', JSON.stringify(answers));

            // Small delay for UX transition (Typeform feel)
            setTimeout(function() {
                $('#q-slide-' + idx).fadeOut(200, function() {
                    currentIdx++;
                    if(currentIdx < total) {
                        $('#q-slide-' + currentIdx).fadeIn(200, function() {
                            isAnimating = false; // Re-enable clicks after next question is fully visible
                        });
                        updateProgress();
                    } else {
                        // Finished
                        updateProgress();
                        submitTest();
                        // Intentionally not setting isAnimating to false here so buttons become permanently disabled
                    }
                });
            }, 300);
        });

        $btnPrev.on('click', function() {
            if (isAnimating) return;
            isAnimating = true;

            if(currentIdx > 0) {
                $('#q-slide-' + currentIdx).hide();
                currentIdx--;
                $('#q-slide-' + currentIdx).fadeIn(200, function() {
                    isAnimating = false;
                });
                updateProgress();
            } else {
                isAnimating = false;
            }
        });

        // Init
        renderQuestions();
    });
})(jQuery);
