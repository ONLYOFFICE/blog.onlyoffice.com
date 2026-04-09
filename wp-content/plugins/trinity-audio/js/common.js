function trinityShowStatus(id, statusName) {
  jQuery(`${id} .trinity-status-wrapper .status`).removeClass('show');
  jQuery(`${id} .trinity-status-wrapper .status.${statusName}`).addClass('show');
}
