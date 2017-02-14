/**
 * @file
 * Snippet manager behaviors.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.snippetManager = {
    attach: function (context, settings) {

      /* global CodeMirror */
      if (typeof CodeMirror == 'undefined') {
        alert(Drupal.t('CodeMirror library is not loaded!'));
        return;
      }

      // -- HTML source page.
      var $soureTextArea = $('.snippet-html-source:visible');
      if ($soureTextArea[0]) {
        CodeMirror.fromTextArea($soureTextArea[0], {
          mode: 'text/html',
          lineNumbers: true,
          readOnly: true,
          foldGutter: true,
          fullscreen: true,
          gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter']
        });
        return;
      }

      // -- Edit snippet page.
      var $textArea = $('.snippet-code-textarea:visible');
      if (!$textArea[0]) {
        return;
      }

      function getModes() {
        var modes = {};
        var modesEncoded = $.cookie('snippetModes');
        if (modesEncoded) {
          modes = JSON.parse(modesEncoded);
        }
        return modes;
      }

      function setFullScreen(state) {
        editor.setOption('fullScreen', state);
        $('#sm-button-enlarge').toggle(!state);
        $('#sm-button-shrink').toggle(state);
      }

      function createList(type) {
        var list = '<' + type + '>\n';
        doc.getSelection().split('\n').forEach(function (value) {
          list += '  <li>' + value + '</li>\n';
        });
        list += '</' + type + '>\n';
        doc.replaceSelection(list, doc.getCursor());
      }

      function createToolbar() {
        var $editor = $('.CodeMirror');

        $('<div id="snippet-manager-buttons"></div>')
          .prependTo($editor)
          .load(settings.snippetManager.buttonsPath);

        var $toolbar = $('<div class="snippet-manager-toolbar"></div>')
          .prependTo($editor);

        var buttons = [
          'bold',
          'italic',
          'underline',
          'strikethrough',
          'list-numbered',
          'list-bullet',
          'undo',
          'redo',
          'clear-formatting',
          'enlarge',
          'shrink'
        ];

        buttons.forEach(function (button, title) {
          // @TODO: Add title attribute.
          $('<svg id="sm-button-' + button + '" class="sm-button"><use xlink:href="#icon-' + button + '"></use></svg>')
            .appendTo($toolbar);
        });

        $('<select id="sm-mode"/>')
          .append('<option value="html_twig">HTML/Twig</option>')
          .append('<option value="text/html">HTML</option>')
          .append('<option value="twig">Twig</option>')
          .append('<option value="javascript">JavaScript</option>')
          .append('<option value="css">CSS</option>')
          .val(mode)
          .change(function () {
            var value = $(this).val();
            editor.setOption('mode', value);
            // Save the value to cookie.
            var modes = getModes();
            modes[settings.snippetManager.snippetId] = value;
            $.cookie('snippetModes', JSON.stringify(modes));

          })
          .appendTo($toolbar);

        $('#sm-button-bold').click(function () {
          doc.replaceSelection('<strong>' + doc.getSelection() + '</strong>', doc.getCursor());
        });

        $('#sm-button-italic').click(function () {
          doc.replaceSelection('<em>' + doc.getSelection() + '</em>', doc.getCursor());
        });

        $('#sm-button-underline').click(function () {
          doc.replaceSelection('<span class="text-decoration: underline">' + doc.getSelection() + '</span>', doc.getCursor());
        });

        $('#sm-button-strikethrough').click(function () {
          doc.replaceSelection('<del>' + doc.getSelection() + '</del>', doc.getCursor());
        });

        $('#sm-button-list-numbered').click(function () {
          createList('ul');
        });

        $('#sm-button-list-bullet').click(function () {
          createList('ol');
        });

        $('#sm-button-undo').click(function () {
          doc.undo();
        });

        $('#sm-button-redo').click(function () {
          doc.redo();
        });

        $('#sm-button-clear-formatting').click(function () {
          doc.replaceSelection($('<div>' + doc.getSelection() + '</div>').text(), doc.getCursor());
        });

        $('#sm-button-enlarge').click(function () {
          setFullScreen(true);
        });

        $('#sm-button-shrink').hide().click(function () {
          setFullScreen(false);
        });

      }

      // Bubble error class.
      if ($textArea.hasClass('error')) {
        $textArea.parent().addClass('snippet-error');
      }

      // Remove "required" attribute because the textarea is no focusable.
      $textArea.removeAttr('required');

      // Create HTML/Twig overlay mode.
      CodeMirror.defineMode('html_twig', function (config, parserConfig) {
        return CodeMirror.overlayMode(
          CodeMirror.getMode(config, parserConfig.backdrop || 'text/html'),
          CodeMirror.getMode(config, 'twig')
        );
      });

      var mode = getModes()[settings.snippetManager.snippetId] || settings.snippetManager.codeMirror.mode;

      var editor = CodeMirror.fromTextArea($textArea[0], {
        lineNumbers: true,
        mode: mode,
        theme: settings.snippetManager.codeMirror.theme,
        extraKeys: {
          F11: function (editor) {
            setFullScreen(!editor.getOption('fullScreen'));
          },
          Esc: function (editor) {
            setFullScreen(false);
          }
        }
      });

      var doc = editor.getDoc();

      // Insert variables into editor.
      $('.snippet-variable').click(function () {
        doc.replaceSelection('{{ ' + $(this).text() + ' }}', doc.getCursor());
        return false;
      });

      // Toolbar.
      if (settings.snippetManager.codeMirror.toolbar) {
        createToolbar();
      }

    }
  };

  /**
   * Filters the snippets list by a text input search string.
   */
  Drupal.behaviors.snippetsFilterByName = {
    attach: function (context, settings) {

      var $input = $('[data-drupal-selector="sm-snippet-filter"]');
      var $table = $('[data-drupal-selector="sm-snippet-list"]');
      var $rows = $table.find('tbody tr');

      $table.find('tbody').append('<tr class="empty-row"/>');
      var $emptyRow = $('.empty-row');
      $emptyRow
        .hide()
        .append('<td colspan="' + $table.find('th').length + '">' + Drupal.t('No snippets were found.') + '</td>');

      function filterSnippetList(event) {
        var query = $(event.target).val();
        var regExp = new RegExp(query, 'i');

        if (query.length >= 0) {
          $rows.each(function (index, row) {
            var $row = $(row);
            var name = $row.find('td:eq(0)').text();
            var id = $row.find('td:eq(1)').text();
            $row.toggle(name.search(regExp) !== -1 || id.search(regExp) !== -1);
          });
        }

        $emptyRow.toggle($rows.filter(':visible').length === 0);
      }

      $input.on({keyup: Drupal.debounce(filterSnippetList, 100)});
    }
  };

}(jQuery, Drupal));
