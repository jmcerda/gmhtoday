<?php

namespace Drupal\Tests\snippet_manager\Functional;

/**
 * Snippet form test.
 *
 * @group snippet_manager
 */
class SnippetFormTest extends TestBase {

  /**
   * Tests snippet add/edit form.
   */
  public function testSnippetForm() {
    $this->drupalGet('admin/structure/snippet/add');
    $this->assertPageTitle(t('Add a snippet'));

    $this->assertByXpath('//div[contains(@class, "form-item-label") and label[.="Label"] and input[@name="label"]]');

    $this->assertByXpath('//div[contains(@class, "form-item-code-value") and label[.="Code"] and //textarea[@name="code[value]"]]');
    $this->assertByXpath('//div[contains(@class, "form-item-code-format")]/label[.="Text format"]/../select/option[@selected="selected" and .="Snippet manager test basic format"]');

    $this->assertByXpath('//table[caption[.="Variables"]]/tbody//td[.="Variables are not configured yet."]');
    $this->assertByXpath('//div[contains(@class, "form-actions")]/input[@value="Save"]');

    // Submit form and check if the snippet is rendered.
    $snippet_label = $this->randomMachineName();
    $snippet_id = strtolower($this->randomMachineName());
    $edit = [
      'label' => $snippet_label,
      'id' => $snippet_id,
      'code[value]' => '<div>2 + 3 = {{ 2 + 3 }}</div>',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertStatusMessage(t('Snippet %label has been created.', ['%label' => $snippet_label]));

    $this->assertSession()->addressEquals('admin/structure/snippet/' . $snippet_id);

    // Edit form.
    $this->drupalGet("admin/structure/snippet/$snippet_id/edit");

    $this->assertPageTitle(t('Edit @label', ['@label' => $snippet_label]));

    $this->assertByXpath(sprintf('//div[contains(@class, "form-item-label")]/input[@name="label" and @value="%s"]', $snippet_label));

    $this->assertByXpath('//textarea[@name="code[value]" and .="<div>2 + 3 = {{ 2 + 3 }}</div>"]');

    $this->assertByXpath('//table[caption[.="Variables"]]/tbody//td[.="Variables are not configured yet."]');

    // @TODO: Test page settings form element.
    $this->markTestIncomplete('This test has not been implemented yet.');

    $this->assertByXpath('//div[contains(@class, "form-actions")]/input[@value="Save"]');
    $this->assertByXpath(sprintf('//div[contains(@class, "form-actions")]/a[contains(@href, "/admin/structure/snippet/%s/edit/variable/add") and .="Add variable"]', $snippet_id));
    $this->assertByXpath(sprintf('//div[contains(@class, "form-actions")]/a[contains(@href, "/admin/structure//snippet/%s/delete") and .="Delete"]', $snippet_id));
  }

  /**
   * Tests snippet delete form.
   */
  public function testSnippetDeleteForm() {
    $this->drupalGet('admin/structure/snippet');
    $this->click('//td[.="alpha"]/../td//ul[@class="dropbutton"]/li/a[.="Delete"]');
    $this->assertPageTitle(t('Are you sure you want to delete the snippet %label?', ['%label' => 'Alpha']));
    $this->assertByXpath('//form[contains(., "This action cannot be undone.")]');
    $this->assertByXpath('//form//a[.="Cancel"]');
    $this->assertByXpath('//form//a[contains(@href, "/admin/structure/snippet") and .="Cancel"]');
    $this->drupalPostForm(NULL, [], t('Delete'));
    $this->assertStatusMessage(t('The snippet %label has been deleted.', ['%label' => 'Alpha']));
    $this->assertSession()->elementNotExists('xpath', '//a[contains(., "Alpha")]');
    $this->assertSession()->addressEquals('admin/structure/snippet');
  }

  /**
   * Tests duplication form.
   */
  public function testDuplicateForm() {
    $this->drupalGet('admin/structure/snippet');
    $this->click('//td[.="alpha"]/../td//ul[@class="dropbutton"]/li/a[.="Duplicate"]');
    $this->assertPageTitle('Duplicate snippet');
    $this->assertByXpath('//input[@name = "label" and @value = "Duplicate of Alpha"]');
    $this->drupalPostForm(NULL, ['id' => 'alpha'], 'Duplicate');
    $this->assertErrorMessage('The machine-readable name is already in use. It must be unique.');
    $this->drupalPostForm(NULL, ['id' => 'duplicate_of_alpha'], 'Duplicate');
    $this->assertPageTitle('Edit Duplicate of Alpha');
    $this->assertByXpath('//input[@name = "label" and @value = "Duplicate of Alpha"]');
    $this->assertByXpath('//input[@name = "id" and @value = "duplicate_of_alpha"]');
    $this->assertByXpath('//textarea[@name = "code[value]" and contains(., "<h3>{{ foo }}</h3>")]');
    $this->assertByXpath('//td/a[@class = "snippet-variable" and .= "foo"]');
  }

}
