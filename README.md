# silverstripe-textformatter

This module can be used to format text in default textfields by adding specific tags in between the text to style it in a custom way. 
The formatter will replace the text with ex. <strong>-Tags or spans with a special class for color styles. 
Tags can also be fully customized via the "updateReplaceableTags" extension-hook.

![grafik](https://github.com/user-attachments/assets/948873f4-2ad1-4647-8f9f-119545dd049a)

![grafik](https://github.com/user-attachments/assets/e224b296-5a6e-4f79-8594-2d67a93ebed6)

# How To use:
To display the Formatting hint in the CMS simply add this in you getCMSFields: 
$fields->dataFieldByName('Title')->setDescription(TextFormatter::getFormattingDescription());

To output the formatted Text in the Frontend you need a special function like this one: 
```
public function FrontendTitle()
{
  return TextFormatter::formattedText($this->Title);
}
```
