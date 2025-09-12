define(['core/ajax', 'core/str'], function(Ajax, Str) {
  return {
    init: function(cfg) {
      const cmid = cfg.cmid;
      let submissionid = 0;
      const historyEl = document.getElementById('aiconcept-history');
      const codeEl = document.getElementById('aiconcept-code');
      const submitBtn = document.getElementById('aiconcept-submit');
      const finalEl = document.getElementById('aiconcept-final');
      const finalBtn = document.getElementById('aiconcept-final-submit');

      function append(role, content) {
        const box = document.createElement('div');
        box.className = role === 'student' ? 'student' : 'assistant';
        box.style.border = '1px solid #ddd'; box.style.padding = '8px'; box.style.margin = '8px 0';
        box.innerText = (role.toUpperCase()) + ':\n' + content;
        historyEl.appendChild(box);
        historyEl.scrollTop = historyEl.scrollHeight;
      }

      function send() {
        const code = codeEl.value.trim();
        if (!code) { return; }
        append('student', code);
        submitBtn.disabled = true;
        Ajax.call([{
          methodname: 'mod_aiconcept_submit_and_respond',
          args: { cmid: cmid, submissionid: submissionid, studentcode: code }
        }])[0].then(function(resp) {
          submissionid = resp.submissionid;
          append('assistant', resp.assistant);
        }).catch(function(err) {
          append('assistant', 'Error: ' + (err.message || JSON.stringify(err)));
        }).finally(function(){
          submitBtn.disabled = false;
          codeEl.value='';
        });
      }

      submitBtn.addEventListener('click', send);

      finalBtn.addEventListener('click', function() {
        const code = finalEl.value.trim();
        if (!code || !submissionid) { return; }
        Ajax.call([{
          methodname: 'mod_aiconcept_submit_and_respond',
          args: { cmid: cmid, submissionid: submissionid, studentcode: 'FINAL_SUBMISSION:\n' + code }
        }])[0].then(function(resp){
          append('student', '[Final submitted]\n' + code);
          append('assistant', resp.assistant);
        }).catch(function(err){
          append('assistant', 'Error: ' + (err.message || JSON.stringify(err)));
        });
      });
    }
  };
});
