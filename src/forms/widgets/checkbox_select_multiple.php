<?php
/**
 * Renders checkboxes as unordered list.
 *
 * Each value in $choices renders as <li /> item in <ul /> list.
 *
 * @package Atk14\Forms
 */
class CheckboxSelectMultiple extends SelectMultiple
{
	var $input_type = "select";

	/**
	 * @param array $options
	 * - **escape_labels** - escaping html in checkbox labels [default: true]
	 *
	 */
	function __construct($options = array())
	{
		$options += array(
			"escape_labels" => true
		);
		$this->escape_labels = $options["escape_labels"];
		parent::__construct($options);
	}

	function my_check_test($value)
	{
		return in_array($value, $this->_my_str_values);
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value) || $value==="" || !is_array($value)) {
			$value = array();
		}
		$has_id = is_array($options['attrs']) && isset($options['attrs']['id']);
		$final_attrs = $this->build_attrs($options['attrs']);
		$output = array('<ul class="checkboxes">');
		$choices = my_array_merge(array($this->choices, $options['choices']));
		$str_values = array();
		foreach ($value as $v) {
			if (!in_array((string)$v, $str_values)) {
				$str_values[] = (string)$v;
			}
		}
		$this->_my_str_values = $str_values;

		$i = 0;
		foreach ($choices as $option_value => $option_label) {
			if ($has_id) {
				$final_attrs['id'] = $options['attrs']['id'].'_'.$i;
			}
			$cb = new CheckboxInput(array('attrs'=>$final_attrs, 'check_test'=>array($this, 'my_check_test')));
			$option_value = (string)$option_value;
			$rendered_cb = $cb->render("{$name}[]", $option_value);
			$label = ($this->escape_labels ? forms_htmlspecialchars($option_label) : $option_label);
			$markup = FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4 ? '<li class="checkbox">%checkbox% <label>%label%</label></li>' : '<li class="checkbox"><label>%checkbox% %label%</label></li>';
			$output[] = strtr($markup,array("%checkbox%" => $rendered_cb, "%label%" => $label));
			$i++;
		}
		$output[] = '</ul>';
		return implode("\n", $output);
	}

	function id_for_label($id_)
	{
		if ($id_) {
			$id_ = $id_.'_0';
		}
		return $id_;
	}
}
