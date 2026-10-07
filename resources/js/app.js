import './bootstrap';
const profileSelect = document.querySelector('[data-profile-select]');

if (profileSelect) {
	const form = profileSelect.closest('[data-registration-form]');
	const conditionalFields = form.querySelectorAll('.profile-field');
	const progress = form.querySelector('[data-form-progress]');
	const functionalLabel = form.querySelector('[data-functional-label]');

	const updateProfileFields = () => {
		const profile = profileSelect.value;

		conditionalFields.forEach((field) => {
			const visible = (field.dataset.profile || '').split(' ').includes(profile);
			field.hidden = !visible;
			field.querySelectorAll('input, select').forEach((input) => {
				input.disabled = !visible;
				input.required = visible && input.hasAttribute('data-required-profile');
			});
		});

		functionalLabel.textContent = profile === 'aluno' ? 'Matrícula' : profile ? 'SIAPE' : 'Matrícula/SIAPE';
		const required = [...form.querySelectorAll('[required]')].filter((input) => !input.disabled);
		const complete = required.filter((input) => input.type === 'file' ? input.files.length > 0 : input.value.trim() !== '').length;
		progress.textContent = `${complete} de ${required.length} campos obrigatórios preenchidos`;
	};

	form.addEventListener('input', updateProfileFields);
	form.addEventListener('change', updateProfileFields);
	updateProfileFields();
}

document.querySelectorAll('[data-other-select]').forEach((select) => {
	const field = document.getElementById(select.dataset.otherSelect);
	const input = field.querySelector('input');
	const updateOtherField = () => {
		const visible = select.value === 'other';
		field.hidden = !visible;
		input.disabled = !visible;
		input.required = visible;
	};

	select.addEventListener('change', updateOtherField);
	updateOtherField();
});
